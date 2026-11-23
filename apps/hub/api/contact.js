// Sends the hub page's contact form via Resend with 7-layer anti-spam protection
// and Kenya DPA 2019 compliance (#28, #30).

const RECIPIENT = "gitonga@gmail.com";
const ipRateMap = new Map(); // In-memory circuit breaker: ip -> [timestamps]

function isValidEmail(value) {
  return typeof value === "string" && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) && value.length <= 200;
}

function sanitizeHeader(val) {
  return String(val || "").replace(/[\r\n<>",;]/g, "").trim();
}

export default async function handler(req, res) {
  if (req.method !== "POST") {
    res.status(405).json({ error: "Method not allowed" });
    return;
  }

  const { name, email, message, company, dpa_consent } = req.body || {};

  // Layer 1 Honeypot: disguised company field
  if (company) {
    res.status(200).json({ ok: true });
    return;
  }

  // Kenya DPA 2019 Section 29 & 32 consent verification
  if (!dpa_consent) {
    res.status(400).json({ error: "Please accept the privacy notice to submit your message." });
    return;
  }

  if (
    typeof name !== "string" || !name.trim() ||
    typeof message !== "string" || !message.trim() ||
    !isValidEmail(email)
  ) {
    res.status(400).json({ error: "Please provide your name, a valid email, and a message." });
    return;
  }

  // Field length bounds
  if (name.length > 120 || message.length > 4000) {
    res.status(400).json({ error: "Input exceeds maximum allowed length." });
    return;
  }

  // Layer 4: Heuristics
  if (/https?:\/\/|www\./i.test(name)) {
    res.status(400).json({ error: "Links are not permitted in the Name field." });
    return;
  }

  const urlMatches = message.match(/https?:\/\/[^\s]+/gi) || [];
  if (urlMatches.length > 1) {
    res.status(400).json({ error: "Messages are limited to at most one link." });
    return;
  }

  const blockedTlds = [/\.ru(\/|$)/i, /\.xyz(\/|$)/i, /\.top(\/|$)/i, /\.click(\/|$)/i, /bit\.ly/i, /t\.me/i, /telegra\.ph/i];
  for (const tld of blockedTlds) {
    if (tld.test(message)) {
      res.status(400).json({ error: "Disallowed link or domain in message." });
      return;
    }
  }

  const letters = message.replace(/[^a-zA-Z]/g, "");
  if (letters.length >= 15) {
    const uppers = letters.replace(/[^A-Z]/g, "");
    if ((uppers.length / letters.length) > 0.60) {
      res.status(400).json({ error: "Message rejected due to excessive capitalization." });
      return;
    }
  }

  // Layer 5: Per-IP Circuit Breaker Rate Limiting (<= 2 / 15m)
  const clientIp = req.headers["x-forwarded-for"]?.split(",")[0]?.trim() || req.socket?.remoteAddress || "127.0.0.1";
  const now = Date.now();
  const window15m = 15 * 60 * 1000;
  const timestamps = (ipRateMap.get(clientIp) || []).filter(t => (now - t) < window15m);
  if (timestamps.length >= 2) {
    res.status(429).json({ error: "Rate limit exceeded. Please try again later." });
    return;
  }
  timestamps.push(now);
  ipRateMap.set(clientIp, timestamps);

  if (!process.env.RESEND_API_KEY) {
    console.error("RESEND_API_KEY is not configured");
    res.status(500).json({ error: "Contact service is not configured yet." });
    return;
  }

  const cleanName = sanitizeHeader(name);
  const cleanEmail = sanitizeHeader(email);

  try {
    const r = await fetch("https://api.resend.com/emails", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${process.env.RESEND_API_KEY}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        from: process.env.FROM_EMAIL || "ericgitonga.com <onboarding@resend.dev>",
        to: [RECIPIENT],
        reply_to: cleanEmail,
        subject: `ericgitonga.com contact message: ${cleanName}`,
        // Raw IP omitted per Kenya DPA 2019 data minimization
        text: `From: ${cleanName} <${cleanEmail}>\nDate: ${new Date().toISOString()}\n\n${message.trim()}`,
      }),
    });

    if (!r.ok) {
      console.error(`Resend dispatch returned HTTP ${r.status}`);
      res.status(502).json({ error: "Could not send your message. Please try again shortly." });
      return;
    }

    res.status(200).json({ ok: true });
  } catch (err) {
    console.error("Error during contact dispatch");
    res.status(500).json({ error: "Could not send your message. Please try again shortly." });
  }
}
