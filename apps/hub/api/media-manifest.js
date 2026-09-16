// Thin same-origin proxy so the static gallery pages can fetch a stable
// "/api/media-manifest" URL without knowing the current Vercel Blob URL
// that api/sync-media.js publishes to.

import { head } from "@vercel/blob";

export default async function handler(req, res) {
  try {
    const blob = await head("media-manifest.json");
    const r = await fetch(blob.url, { cache: "no-store" });
    if (!r.ok) {
      res.status(502).json({ error: "Could not fetch media manifest" });
      return;
    }
    const manifest = await r.json();
    res.setHeader(
      "Cache-Control",
      "public, max-age=300, stale-while-revalidate=3600",
    );
    res.status(200).json(manifest);
  } catch (err) {
    console.error("media-manifest proxy failed:", err);
    res.status(404).json({ error: "Media manifest not generated yet" });
  }
}
