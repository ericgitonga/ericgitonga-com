# Changelog

All notable changes to this project are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project
adheres to [Semantic Versioning](https://semver.org) (pre-1.0: MINOR = new features/user-facing
behaviour, PATCH = fixes/docs/housekeeping). One shared version across every app in `apps/` — any
change to any component bumps this same number, by design (see README's "Versioning" section).

## [0.7.1] - 2026-11-27

### Added
- **Desktop Native & Web GUI Uploaders**: Added `scripts/upload_photo_gui.py` (Tkinter native desktop app with image thumbnail preview) and `scripts/upload_gui_web.py` (Streamlit browser app with drag-and-drop) for 1-click photo intake.
- **Media Upload Guide**: Authored comprehensive documentation in `docs/MEDIA_UPLOAD_GUIDE.md` detailing architecture, direct upload workflows, album conventions, cover handling, and troubleshooting.

## [0.7.0] - 2026-11-27

### Added & Enhanced
- **Dynamic Drops Manifest Crawling**: Updated `apps/hub/api/media-manifest.js` to serve dynamic Drops collections crawled from Angry Hosting via Vercel Blob with seamless fallback to bundled static assets.
- **FTPS Intermediate TLS Validation for Media Sync**: Bundled Let's Encrypt Gen-Y intermediate certificate chain in `apps/hub/api/sync-media.js` and `letsencrypt_geny_intermediates.pem`, resolving unchained server cert verification so the daily/on-demand media sync succeeds strictly without TLS errors.
- **Protected Remote Drops Storage**: Added `Drops/` and `Daubs/` to `FORBIDDEN_PREFIXES` in `.github/scripts/ftps_deploy.py` to prevent code deployment workflows from modifying or retiring client-uploaded media directories.
- **Direct CLI Photo Uploader (`scripts/upload_plate_photo.py`)**: Added an automated 1-click photo uploader tool that connects to Angry Hosting via strict TLS 1.2+ FTPS, verifies remote byte size, and triggers an instant Vercel Blob manifest rebuild via `/api/sync-media`—bypassing Git commits, PRs, and manual redeployments completely.

## [0.6.2] - 2026-11-27

### Added
- **Water Droplet Collision Art Addition (`d_18.jpg`)**: Catalogued droplet collision photograph `d_18.jpg` (`/home/gitonga/Pictures/Yangu/Water Drops/Drops/d_18.jpg`) into `apps/hub/assets/drops/` and `public/media/drops/Drops/`, and registered it in `apps/hub/api/media-manifest.js` and `content/media/manifest.json`.

## [0.6.1] - 2026-11-26

### Added & Changed
- **3-over-2 Centered Wall Layout**: Formatted the homepage wall into an architectural 6-column grid with equal-height rows, displaying the first three plates (Plate I: Dudus, Plate II: Drops, Plate III: Daguerreotypes) across the top row, and centering the last two plates (Plate IV: Daubs, Plate V: Diffs) directly below them.
- **Updated Drops Hero & Album Cover (`d_03.jpg`)**: Adopted droplet collision sculpture `d_03.jpg` (`/home/gitonga/Pictures/Yangu/Water Drops/Drops/d_03.jpg`) as the hero cover across `apps/hub/assets/hub/drops.jpg` and `public/assets/hub/drops.jpg`, as well as the album cover thumbnail.
- **Direct Edge CDN Media Accessibility for Drops**: Bundled all 17 high-speed water droplet collision art photographs into `apps/hub/assets/drops/` for immediate global edge delivery, registered the collection in `apps/hub/api/media-manifest.js`, added `Drops` to `PLATES` in `apps/hub/api/sync-media.js`, and synced `content/media/manifest.json`.

## [0.6.0] - 2026-11-26

### Added
- **Plate II: Drops (High-Speed Droplet Art Gallery)**: Positioned Drops immediately following Dudus across the home wall and top navigation bar, sequencing plates as Plate I (Dudus), Plate II (Drops), Plate III (Daguerreotypes), Plate IV (Daubs), and Plate V (Diffs).
- **Web-Optimized Drops Collection**: Processed and catalogued 17 high-speed water droplet collision photographs from `/home/gitonga/Pictures/Yangu/Drops` into `public/media/drops/Drops/`, generated album thumbnail `cover.jpg`, and added hero plate card `public/assets/hub/drops.jpg`.
- **Media Manifest Registration**: Extended `content/media/manifest.json` and `MediaService.php` with the `drops` plate specification.
- **Interactive Gallery & Route**: Added `/drops` route dispatched via `HubController::drops()`, rendered through `src/Views/gallery/drops.php` with Dan Winters modal viewer (`initGallery('drops')`).
- **5-Column Wall & Unified Navbar**: Updated the home wall grid in `src/Views/hub/index.php` and `apps/hub/index.html` to support 5 plates with responsive breakpoints, and integrated `DROPS` across the topbar navigation.

## [0.5.0] - 2026-11-23

### Security & Privacy (Adversarial Audit Remediations)
- **Eliminated Production Credential Literals in CI**: Removed plaintext password and secret values from `.github/workflows/deploy-prod.yml`, loading config strictly from environment secrets (`PROD_ENV_CONTENT`) (#25).
- **Hardened FTPS Deployment TLS**: Replaced unverified FTPS peer settings in `.github/workflows/deploy-prod.yml` with strict TLS 1.2+ certificate chain validation (`connect_verified_ftps`) targeting `angryhosting.com` with Let's Encrypt Gen-Y intermediate bundle (#26).
- **Atomic Order Persistence & Verification Status**: Added `orders` schema table in `Database.php`, persisted checkout orders with unique ID (`ord_...`) and status `pending_verification`, and sanitized notification email subjects (#27).
- **7-Layer Anti-Spam Gate Integration**: Extended workspace anti-spam verification to shop checkout (`checkout.php`, `ShopController.php`) and hardened Vercel Hub contact form (`apps/hub/api/contact.js`) with heuristic filters, shouting detection, length bounds, and circuit-breaker rate limits (#28).
- **Gallery Cron & FTPS Security**: Enforced mandatory `CRON_SECRET` authorization (failing closed if unset), restricted HTTP methods, and defaulted FTPS connection to strict TLS certificate validation in `apps/hub/api/sync-media.js` (#29).
- **Kenya DPA 2019 Section 29 Compliance & IP Redaction**: Added statutory privacy notices and explicit consent checkboxes across contact dialogs (`modal-contact.php`, `apps/hub/index.html`) and checkout (`checkout.php`). Redacted raw visitor IP from email notification bodies and added automatic log rotation/redaction in `Mailer.php` (#30).
- **Deployment Maintenance Gate**: Added front controller check in `public/index.php` for `.maintenance_deploy.json`, returning HTTP 503 with `Retry-After: 5` and a clean status message during file promotion (#31).
- **Rate Quota Hardening & Child Telemetry Consent Gate**: Enforced per-IP rate limit quotas (<= 2 / 15m, <= 5 / 24h, global <= 15 / 1h) in `AntiSpamGate.php`, bounded cart quantities, sanitized Markdown links/scripts in `MarkdownParser.php`, added on-device privacy reassurance notice in `PhotoCapture.tsx`, and consent-gated Vercel telemetry in `apps/dudus/src/app/layout.tsx` (#32).
- Closes #24, closes #25, closes #26, closes #27, closes #28, closes #29, closes #30, closes #31, closes #32.

## [0.4.3] - 2026-11-20

### Changed
- Updated Diffs (`/diffs`) project portfolio:
  - Added Ndingi Foundation (`https://ndingifoundation.org`).
  - Added Men Let's Talk (`https://www.menletstalk.co.ke`).
  - Retained Busherian Biasharas (`https://www.busherianbiasharas.com`).
  - Removed Career-Transition and En Mascaradores entries.

## [0.4.2] - 2026-11-20

### Changed
- Standardized topbar header across all pages to unified 2-group Dan Winters minimalist layout matching reference design:
  - Left: `DUDUS    DAGUERREOTYPES    DAUBS    DIFFS` (all uppercase monospace, with active plate highlighted).
  - Right: `Bio    Blog    Shop    Contact` (title case monospace, Bio/Contact opening global dialogs, Blog/Shop linking to their sections).
  - Removed obsolete `topbar-home` ("ERIC GITONGA") from header bar; linked author credit in footer to `/`.
  - Removed duplicate and legacy `← ERIC GITONGA / ...` breadcrumbs from top-level plate and section pages (`/daubs`, `/diffs`, `/daguerreotypes`, `/dudus`, `/blog`, `/shop`).
  - Streamlined nested subpage breadcrumbs to point cleanly to parent sections (`← DUDUS`, `← BLOG`, `← SHOP`).
- Added unit test asserting topbar header uniformity and link presence across all views.

## [0.4.1] - 2026-11-20

### Added
- Replaced placeholder on Daubs (`/daubs`) with interactive album grid (`initGallery("daubs")`) and lightbox modal viewer.
- Integrated comprehensive production media manifest (`content/media/manifest.json`) covering all 18 Dudus albums, 3 Daguerreotypes albums, and 4 Daubs albums.
- Bundled ~3 sample items per section in `public/media/`, `public/photos/`, and `public/static/` for an authentic, self-contained preview experience without overwhelming storage.
- Mapped `photo_ref` in `DudusCatalogService` to ensure all 24 dudu species cards display their macro photographs.
- Added unit test suite for `MediaService` manifest integrity across all three plates.

## [0.4.0] - 2026-11-20

### Added
- Complete unified PHP 8.3 architecture on `preview` branch for `preview.ericgitonga.com`.
- Front Controller (`public/index.php`) and clean URL router (`src/Router.php`) with zero framework bloat.
- Abolished all subdomains into unified paths:
  - Dudus companion moved from `dudus.ericgitonga.com` to `ericgitonga.com/dudus/*` (`/dudus`, `/dudus/identify`, `/dudus/card/{id}`, `/dudus/orders/{order}`, `/dudus/search`, `/dudus/dutraits`).
  - Shop moved from `shop.ericgitonga.com` to `ericgitonga.com/shop/*` (`/shop`, `/shop/prints`, `/shop/daguerreotypes`, `/shop/apparel`, `/shop/coasters`, `/shop/cart`, `/shop/checkout`).
- Ported Flask print shop into native PHP session cart with 30% non-Dudu print discount rule, living-room wall mockup scaling, and SQLite M-Pesa anti-replay ledger.
- Ported dynamic Markdown blog engine (`src/Services/BlogService.php`) directly rendering `content/blog/*.md`.
- Enforced 7-layer public form anti-spam defense gate (`src/Security/AntiSpamGate.php`) on the Contact modal.
- Added strict media protection guards in `ftps_deploy.py` permanently forbidding mutation/deletion of `Daguerreotypes/`, `Dudus/`, `media/`, and uploads.
- Added automated unit test suite (`tests/run_unit_tests.php`) covering all 14 core subsystems.
- Added direct slug routing `/dudus/{id}` and route shortcuts for `/cart` and `/checkout`.
- Enforced isolated SQLite database for preview and CI unit tests.

## [0.3.4] - 2026-11-05

### Fixed

- Point the homepage `Shop` link in `apps/dudus` to `https://shop.ericgitonga.com/prints` instead of the deprecated `shop.dudus.ericgitonga.com` subdomain (closes #22).

## [0.3.3] - 2026-09-16

### Added

- Fix linking to Daubs in top navbar.

## [0.3.2] - 2026-09-16

### Added

- Fix PLATE numbering in pages after the Dudus move.


## [0.3.1] - 2026-09-16

### Added

- Moved Dudus to the left/front/top of all pages

## [0.3.0] - 2026-09-16

### Added

- Wired up Daubs so it displays art work.

## [0.2.0] - 2026-09-16

### Added

- Second app migrated into the monorepo: `apps/dudus` (formerly `dudus-app`), full commit
  history preserved via `git subtree` (78 commits: 77 original + 1 merge). CI split into three
  app-prefixed workflows (`dudus-e2e`, `dudus-unit`, `dudus-unit-dudu-intake`), running
  unconditionally rather than path-filtered — a required check that's path-filtered would stay
  permanently "expected" (and block merge) on any PR that doesn't touch this app; `dudus-app`'s
  own `tools/dudu-intake` workflow had already documented this exact constraint before the
  migration. `dudus-app`'s own VERSION/CHANGELOG/tag history stops here — this repo's shared
  version now covers it going forward. `apps/dudus/tools/dudu-intake` keeps its own independent
  versioning (`intake-vX.Y.Z`) unchanged, same nested-exception pattern as before the migration.

## [0.1.0] - 2026-09-16

### Added

- First app migrated into the monorepo: `apps/hub` (formerly `eric-gitonga-links`), full commit
  history preserved via `git subtree`. A trial run before deciding whether `dudus-app` and
  `dudu-merchandise` follow.
