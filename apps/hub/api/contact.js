// Sends the hub page's contact form via Resend, so a visitor's message reaches
// gitonga@gmail.com without them needing their own mail client open — same
// approach as career-transition's intake pipeline. Requires RESEND_API_KEY
// (and optionally FROM_EMAIL, default "ericgitonga.com <onboarding@resend.dev>") set as a
// Vercel project env var.

const RECIPIENT = "gitonga@gmail.com";

function isValidEmail(value) {
  return typeof value === "string" && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

export default async function handler(req, res) {
  if (req.method !== "POST") {
    res.status(405).json({ error: "Method not allowed" });
    return;
  }

  const { name, email, message, company } = req.body || {};

  // Honeypot: a hidden field real visitors never fill in. A bot that fills
  // every field gets a fake success instead of a signal to try again.
  if (company) {
    res.status(200).json({ ok: true });
    return;
  }

  if (
    typeof name !== "string" || !name.trim() ||
    typeof message !== "string" || !message.trim() ||
    !isValidEmail(email)
  ) {
    res.status(400).json({ error: "Please fill in every field with a valid email." });
    return;
  }

  if (!process.env.RESEND_API_KEY) {
    console.error("RESEND_API_KEY is not set");
    res.status(500).json({ error: "Contact form isn't configured yet." });
    return;
  }

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
        reply_to: email,
        subject: `ericgitonga.com contact form: ${name}`,
        text: `From: ${name} <${email}>\n\n${message}`,
      }),
    });

    if (!r.ok) {
      console.error("Resend error:", await r.text());
      res.status(502).json({ error: "Could not send your message. Please try again shortly." });
      return;
    }

    res.status(200).json({ ok: true });
  } catch (err) {
    console.error("Contact form error:", err);
    res.status(500).json({ error: "Could not send your message. Please try again shortly." });
  }
}
