// Thin same-origin proxy so the static gallery pages can fetch a stable
// "/api/media-manifest" URL without knowing the current Vercel Blob URL
// that api/sync-media.js publishes to.

import { head } from "@vercel/blob";

const DROPS_ALBUM = [
  {
    slug: "Drops",
    title: "Drops",
    cover: "/assets/drops/cover.jpg",
    images: [
      { filename: "d_01.jpg", url: "/assets/drops/d_01.jpg" },
      { filename: "d_02.jpg", url: "/assets/drops/d_02.jpg" },
      { filename: "d_03.jpg", url: "/assets/drops/d_03.jpg" },
      { filename: "d_04.jpg", url: "/assets/drops/d_04.jpg" },
      { filename: "d_05.jpg", url: "/assets/drops/d_05.jpg" },
      { filename: "d_06.jpg", url: "/assets/drops/d_06.jpg" },
      { filename: "d_07.jpg", url: "/assets/drops/d_07.jpg" },
      { filename: "d_08.jpg", url: "/assets/drops/d_08.jpg" },
      { filename: "d_09.jpg", url: "/assets/drops/d_09.jpg" },
      { filename: "d_10.jpg", url: "/assets/drops/d_10.jpg" },
      { filename: "d_11.jpg", url: "/assets/drops/d_11.jpg" },
      { filename: "d_12.jpg", url: "/assets/drops/d_12.jpg" },
      { filename: "d_13.jpg", url: "/assets/drops/d_13.jpg" },
      { filename: "d_14.jpg", url: "/assets/drops/d_14.jpg" },
      { filename: "d_15.jpg", url: "/assets/drops/d_15.jpg" },
      { filename: "d_16.jpg", url: "/assets/drops/d_16.jpg" },
      { filename: "d_17.jpg", url: "/assets/drops/d_17.jpg" },
      { filename: "d_18.jpg", url: "/assets/drops/d_18.jpg" }
    ]
  }
];

export default async function handler(req, res) {
  try {
    let manifest = { generatedAt: new Date().toISOString(), plates: {} };
    try {
      const blob = await head("media-manifest.json");
      const r = await fetch(blob.url, { cache: "no-store" });
      if (r.ok) {
        manifest = await r.json();
      }
    } catch (blobErr) {
      console.warn("Blob manifest fetch skipped or unavailable:", blobErr);
    }

    if (!manifest.plates) {
      manifest.plates = {};
    }
    // Always guarantee Drops gallery is populated and served from static assets
    manifest.plates.drops = DROPS_ALBUM;

    res.setHeader(
      "Cache-Control",
      "public, max-age=300, stale-while-revalidate=3600",
    );
    res.status(200).json(manifest);
  } catch (err) {
    console.error("media-manifest proxy failed:", err);
    res.status(200).json({
      generatedAt: new Date().toISOString(),
      plates: { drops: DROPS_ALBUM }
    });
  }
}
