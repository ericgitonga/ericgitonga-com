# Changelog

All notable changes to this project are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project
adheres to [Semantic Versioning](https://semver.org) (pre-1.0: MINOR = new features/user-facing
behaviour, PATCH = fixes/docs/housekeeping). One shared version across every app in `apps/` — any
change to any component bumps this same number, by design (see README's "Versioning" section).

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
