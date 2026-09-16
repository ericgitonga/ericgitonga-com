// Nightly Vercel Cron job (see vercel.json) that crawls Angry Hosting over
// FTP(S) for the Daguerreotypes/Dudus media, and republishes a manifest to
// Vercel Blob for the gallery pages to read via /api/media-manifest.
//
// Folder convention on Angry Hosting (one level of albums, no further
// nesting): <ANGRYHOSTING_FTP_BASE_PATH>/Daguerreotypes/<album>/<image> and
// .../Dudus/<album>/<image>. An optional cover.<ext> file per album sets its
// grid thumbnail; otherwise the first image alphabetically is used.
//
// Requires env vars: ANGRYHOSTING_FTP_HOST, ANGRYHOSTING_FTP_USER,
// ANGRYHOSTING_FTP_PASSWORD, MEDIA_BASE_URL, CRON_SECRET. Optional:
// ANGRYHOSTING_FTP_BASE_PATH (default ""), ANGRYHOSTING_FTP_SECURE
// (default "true" — set to "false" only if the account can't do FTPS).

import { Client } from "basic-ftp";
import { put, head } from "@vercel/blob";
import sharp from "sharp";
import { PassThrough } from "node:stream";

const PLATES = ["Daguerreotypes", "Dudus"];
const IMAGE_EXTENSIONS = new Set([".jpg", ".jpeg", ".png", ".webp", ".gif"]);
const THUMBNAIL_WIDTH = 400;
const THUMBNAIL_QUALITY = 78;

function isImage(filename) {
  const dot = filename.lastIndexOf(".");
  if (dot === -1) return false;
  return IMAGE_EXTENSIONS.has(filename.slice(dot).toLowerCase());
}

function isCover(filename) {
  const dot = filename.lastIndexOf(".");
  const base = dot === -1 ? filename : filename.slice(0, dot);
  return base.toLowerCase() === "cover";
}

function titleCase(slug) {
  return slug
    .split("-")
    .filter(Boolean)
    .map((word) => word[0].toUpperCase() + word.slice(1))
    .join(" ");
}

function imageUrl(basePath, plate, album, filename) {
  return `${process.env.MEDIA_BASE_URL}/${plate}/${encodeURIComponent(album)}/${encodeURIComponent(filename)}`;
}

async function downloadToBuffer(client, remotePath) {
  const chunks = [];
  const stream = new PassThrough();
  stream.on("data", (chunk) => chunks.push(chunk));
  await client.downloadTo(stream, remotePath);
  return Buffer.concat(chunks);
}

// Grid thumbnails are generated and hosted on Vercel Blob (fast CDN) instead
// of hotlinking the full original from Angry Hosting — a grid of a dozen-plus
// multi-MB originals made the gallery feel very slow. Falls back to the
// original hotlinked URL if the download/resize fails for any reason, so one
// bad file doesn't break the whole sync. The modal's full-size images are
// unaffected — still hotlinked directly, since only one loads at a time.
//
// Skips regenerating a thumbnail that already exists at this album's stable
// path — otherwise every album gets re-downloaded and re-resized on every
// single daily sync forever, which will eventually exceed Vercel's function
// duration limit as more albums pile up. Trade-off: if you swap an album's
// cover file for a different photo, its thumbnail won't update on its own —
// delete `thumbnails/<plate>/<slug>.jpg` from the Blob store to force a
// refresh.
async function buildThumbnail(client, remotePath, plate, albumSlug) {
  const thumbnailPath = `thumbnails/${plate}/${albumSlug}.jpg`;

  try {
    const existing = await head(thumbnailPath);
    if (existing) return existing.url;
  } catch (err) {
    // Not found — fall through and generate it.
  }

  try {
    const original = await downloadToBuffer(client, remotePath);
    const resized = await sharp(original)
      .resize({ width: THUMBNAIL_WIDTH, withoutEnlargement: true })
      .jpeg({ quality: THUMBNAIL_QUALITY })
      .toBuffer();
    const blob = await put(thumbnailPath, resized, {
      access: "public",
      addRandomSuffix: false,
      allowOverwrite: true,
      contentType: "image/jpeg",
    });
    return blob.url;
  } catch (err) {
    console.error(`Thumbnail failed for ${remotePath}:`, err);
    return null;
  }
}

async function listAlbums(client, basePath, plate) {
  const plateDir = basePath ? `${basePath}/${plate}` : plate;
  const albumDirs = (await client.list(plateDir)).filter((e) => e.isDirectory);
  const albums = [];

  for (const dir of albumDirs) {
    const albumDir = `${plateDir}/${dir.name}`;
    const files = (await client.list(albumDir)).filter(
      (e) => e.isFile && isImage(e.name),
    );

    const cover = files.find((e) => isCover(e.name));
    const images = files
      .filter((e) => e !== cover)
      .sort((a, b) => a.name.localeCompare(b.name))
      .map((e) => ({
        filename: e.name,
        url: imageUrl(basePath, plate, dir.name, e.name),
      }));

    if (images.length === 0) continue;

    const coverFilename = cover ? cover.name : images[0].filename;
    const coverRemotePath = `${albumDir}/${coverFilename}`;
    const coverFallbackUrl = cover
      ? imageUrl(basePath, plate, dir.name, cover.name)
      : images[0].url;
    const thumbnailUrl = await buildThumbnail(client, coverRemotePath, plate, dir.name);

    albums.push({
      slug: dir.name,
      title: titleCase(dir.name),
      cover: thumbnailUrl || coverFallbackUrl,
      images,
    });
  }

  return albums.sort((a, b) => a.slug.localeCompare(b.slug));
}

export default async function handler(req, res) {
  if (
    process.env.CRON_SECRET &&
    req.headers.authorization !== `Bearer ${process.env.CRON_SECRET}`
  ) {
    res.status(401).json({ error: "Unauthorized" });
    return;
  }

  const required = [
    "ANGRYHOSTING_FTP_HOST",
    "ANGRYHOSTING_FTP_USER",
    "ANGRYHOSTING_FTP_PASSWORD",
    "MEDIA_BASE_URL",
  ];
  const missing = required.filter((name) => !process.env[name]);
  if (missing.length) {
    console.error("Missing env vars:", missing.join(", "));
    res.status(500).json({ error: `Missing env vars: ${missing.join(", ")}` });
    return;
  }

  const basePath = (process.env.ANGRYHOSTING_FTP_BASE_PATH || "").replace(/\/+$/, "");
  const client = new Client();

  try {
    await client.access({
      host: process.env.ANGRYHOSTING_FTP_HOST,
      user: process.env.ANGRYHOSTING_FTP_USER,
      password: process.env.ANGRYHOSTING_FTP_PASSWORD,
      secure: process.env.ANGRYHOSTING_FTP_SECURE !== "false",
      // Shared hosting FTPS commonly serves a cert Node's default CA store
      // can't chain-verify. Read-only media crawl over an otherwise-encrypted
      // channel, so this is an accepted tradeoff unless explicitly tightened.
      secureOptions: {
        rejectUnauthorized: process.env.ANGRYHOSTING_FTP_REJECT_UNAUTHORIZED === "true",
      },
    });

    const plates = {};
    for (const plate of PLATES) {
      plates[plate.toLowerCase()] = await listAlbums(client, basePath, plate);
    }

    const manifest = { generatedAt: new Date().toISOString(), plates };

    const blob = await put("media-manifest.json", JSON.stringify(manifest), {
      access: "public",
      addRandomSuffix: false,
      allowOverwrite: true,
      contentType: "application/json",
    });

    res.status(200).json({
      ok: true,
      url: blob.url,
      albumCounts: Object.fromEntries(
        Object.entries(plates).map(([plate, albums]) => [plate, albums.length]),
      ),
    });
  } catch (err) {
    console.error("Media sync failed:", err);
    res.status(502).json({ error: "Media sync failed", detail: String(err) });
  } finally {
    client.close();
  }
}
