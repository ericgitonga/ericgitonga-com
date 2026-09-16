# Changelog

All notable changes to this project are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project
adheres to [Semantic Versioning](https://semver.org) (pre-1.0: MINOR = new features/user-facing
behaviour, PATCH = fixes/docs/housekeeping). One shared version across every app in `apps/` — any
change to any component bumps this same number, by design (see README's "Versioning" section).

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
