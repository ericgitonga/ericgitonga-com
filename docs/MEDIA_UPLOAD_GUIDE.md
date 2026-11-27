# Media Upload & Gallery Management Guide

This guide explains how to add new photographs and artworks to the galleries on **[ericgitonga.com](https://ericgitonga.com)** directly, without creating Git commits, tickets, PRs, or triggering code deployments.

---

## 1. Architecture Overview

Media intake on `ericgitonga.com` is completely decoupled from application code:

```
┌─────────────────────────────────┐
│     Your Local Machine          │
│  (Desktop GUI, Web GUI, or CLI) │
└────────────────┬────────────────┘
                 │
                 │ 1. Upload via strict FTPS (TLS 1.2+)
                 ▼
┌─────────────────────────────────┐
│   Angry Hosting Media Server    │
│    (media.ericgitonga.com)      │
│  Stores full-res originals      │
└────────────────┬────────────────┘
                 │
                 │ 2. Trigger /api/sync-media webhook
                 ▼
┌─────────────────────────────────┐
│        Vercel Serverless        │
│  • Crawls albums via FTPS       │
│  • Generates 400px webp/jpg     │
│  • Publishes JSON to Blob CDN   │
└────────────────┬────────────────┘
                 │
                 │ 3. Fetched by client (/api/media-manifest)
                 ▼
┌─────────────────────────────────┐
│     Live Gallery Pages          │
│  Drops, Daguerreotypes, Daubs   │
└─────────────────────────────────┘
```

- **Original Photographs**: Stored directly on Angry Hosting under `media.ericgitonga.com`.
- **Thumbnails & Indexing**: Automatically generated and cached globally on Vercel Blob CDN.
- **Galleries**: Fetch `/api/media-manifest` dynamically on page load.

---

## 2. Upload Methods

You can upload new photos using any of the three tools below based on your preference.

### Option A: Desktop Native GUI (Recommended for Everyday Use)
A lightweight desktop app with file browsing, live thumbnail preview, metadata inspection, and single-click upload.

```bash
python scripts/upload_photo_gui.py
```

**Features:**
- **Visual File Browser**: Select any `.jpg`, `.jpeg`, `.png`, or `.webp` file.
- **Image Preview**: Displays image dimensions, file size, format, and an instant thumbnail.
- **Plate Selector**: Dropdown pre-configured for `Drops`, `Daguerreotypes`, `Daubs`, and `Dudus`.
- **Album Auto-Fill**: Automatically fills the standard album name based on the selected plate.
- **Live Progress & Logs**: Displays real-time connection status, byte verification, and Vercel Blob sync confirmations.
- **One-Click Browser Verification**: Prompts to open the live gallery page the moment the upload completes.

---

### Option B: Streamlit Web Drag-and-Drop GUI (Browser-Based)
A browser interface with drag-and-drop file upload, side-by-side inspection, and sidebar settings.

```bash
conda run -n ds streamlit run scripts/upload_gui_web.py
```

**Features:**
- Drag-and-drop directly from your file manager.
- Runs inside the `ds` conda environment.
- Balloon celebration and direct clickable live URL on completion.

---

### Option C: Terminal CLI (Fastest for Scripting or Single Files)
A 1-line command in your terminal.

```bash
# Upload a new drop photograph (defaults to Plate: Drops, Album: Drops)
python scripts/upload_plate_photo.py /path/to/d_19.jpg

# Upload to a specific plate and album
python scripts/upload_plate_photo.py /path/to/portrait.jpg --plate Daguerreotypes --album "Beauty In The Ordinary"

# Custom destination filename
python scripts/upload_plate_photo.py /path/to/photo.jpg --plate Daubs --album Watercolours --filename wc_16.jpg

# Upload without triggering immediate Vercel sync (let nightly cron handle it)
python scripts/upload_plate_photo.py /path/to/photo.jpg --no-sync
```

---

## 3. Directory Structure & Album Conventions

On the Angry Hosting server (`media.ericgitonga.com`), files are organized into a strict 2-level folder hierarchy: `<Plate>/<Album>/<filename>`:

| Plate | Folder Path | Typical Filename Pattern | Example Public URL |
| :--- | :--- | :--- | :--- |
| **Drops** | `Drops/Drops/` | `d_01.jpg`, `d_02.jpg`, ... | `https://media.ericgitonga.com/Drops/Drops/d_19.jpg` |
| **Daguerreotypes** | `Daguerreotypes/<Album>/` | Descriptive or sequential | `https://media.ericgitonga.com/Daguerreotypes/Beauty%20In%20The%20Ordinary/dsc_01.jpg` |
| **Daubs** | `Daubs/<Album>/` | `dr_01.jpg`, `wc_01.jpg`, ... | `https://media.ericgitonga.com/Daubs/Watercolours/wc_16.jpg` |
| **Dudus** | `Dudus/<Order>/` | Species names or codes | `https://media.ericgitonga.com/Dudus/Bees/be-001.jpg` |

### Setting an Album Cover
- To set a custom card cover for an album in the gallery grid, include an image named **`cover.jpg`** (or `cover.png`/`cover.webp`) inside that album's folder.
- If no `cover` image is provided, the system automatically uses the **first photo alphabetically** as the album cover.
- 400px thumbnails are generated automatically and served from Vercel Blob CDN for instant page loads.

---

## 4. Credentials & Local Configuration

The upload tools read credentials automatically in this order of precedence:

1. Environment variables (`ANGRYHOSTING_FTP_HOST`, `ANGRYHOSTING_FTP_USER`, `ANGRYHOSTING_FTP_PASSWORD`, `CRON_SECRET`).
2. Local config file: `~/.config/ericgitonga/ftp.env` (stored with permissions `0600`).
3. Repository environment files: `.env.local`.

If credentials are not yet present, the GUI or CLI tool will prompt you once and save them securely to `~/.config/ericgitonga/ftp.env` so you never have to type them again.

### Configuration Template (`~/.config/ericgitonga/ftp.env`)
```bash
ANGRYHOSTING_FTP_HOST=angryhosting.com
ANGRYHOSTING_FTP_USER=your_ftp_username
ANGRYHOSTING_FTP_PASSWORD=your_ftp_password
ANGRYHOSTING_FTP_BASE_PATH=ericgitonga.com/media/html
CRON_SECRET=4a1fd472aa93eeefac8287d4daf826fa095fd05dea757a5a8a28dd446b694174
```

---

## 5. Verification & Troubleshooting

### 1. How do I verify my upload is live?
- Check the live gallery directly in your browser:
  - **Drops**: [https://ericgitonga.com/drops](https://ericgitonga.com/drops)
  - **Daguerreotypes**: [https://ericgitonga.com/daguerreotypes](https://ericgitonga.com/daguerreotypes)
  - **Daubs**: [https://ericgitonga.com/daubs](https://ericgitonga.com/daubs)
- Or check the JSON manifest directly: [https://ericgitonga.com/api/media-manifest](https://ericgitonga.com/api/media-manifest)

### 2. The photo was uploaded, but I don't see it on the page:
- **Browser Cache**: Static assets and manifest responses have cache-control headers. Force a hard refresh:
  - Linux/Windows: **`Ctrl + Shift + R`**
  - Mac: **`Cmd + Shift + R`**
- **Manual Sync**: If you uploaded with `--no-sync` or via a generic FTP client like Cyberduck, manually trigger the sync:
  ```bash
  curl -X POST -H "Authorization: Bearer <CRON_SECRET>" https://ericgitonga.com/api/sync-media
  ```

### 3. Safety Guarantee:
- The deployment script (`.github/scripts/ftps_deploy.py`) marks `Daguerreotypes/`, `Dudus/`, `Drops/`, and `Daubs/` as **protected forbidden prefixes**. Code deployments will never overwrite, modify, or delete uploaded media files on the server.
