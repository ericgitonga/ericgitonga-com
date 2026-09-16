# Changelog

All notable changes to this tool are documented in this file, independently of the main
`dudus-app` site's own `CHANGELOG.md` — this tool is never deployed, so it versions on its own
schedule rather than moving in lockstep with site releases. Format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/); [Semantic Versioning](https://semver.org),
pre-1.0. Tags are prefixed `intake-v` to avoid colliding with the site's own `v*` tags.

## [0.10.0] - 2026-08-17

### Added

- Add cards' "Species directory" field now has a "Browse…" button next to it that opens the
  OS's native folder picker (via `tkinter.filedialog`), rooted at `Dudus/`, instead of requiring
  the path to be typed. Guarded behind a `TKINTER_AVAILABLE` check so the tool (and its test
  suite) still runs in environments without a Tk/display, e.g. CI — the button just doesn't
  appear there. Picking a folder inside `Dudus/` fills the field with the same relative-path
  form a typed entry would use. (closes #95)

tag: `intake-v0.10.0`

## [0.9.0] - 2026-08-15

### Added

- Add cards now works from a species directory instead of a browser upload: type a directory
  (under `Dudus/`, or absolute), and the tool scans it for lay/technical report pairs (`*-la.pdf`
  paired with its non-`-la` sibling) and photo files, letting the admin pick when a folder holds
  more than one candidate (e.g. book- vs web-sourced). The technical report is now parsed (same
  H1/H2 convention as the lay report) into a new `technical_sections` field, so the app (#88) can
  render it in a modal. `parse_lay_report` is renamed `parse_report_pdf` since it's now used for
  both report shapes. The "Replace lay report" existing-card flow also re-parses the technical
  sibling on content update. (closes #89)

tag: `intake-v0.9.0`

## [0.8.0] - 2026-08-15

### Added

- Add cards now takes a row's photo automatically from whatever the uploaded lay-report PDF
  already embeds under its taxonomy line (SKILL.md's Photo line convention), running it through
  the same metadata-strip + 3:2-pad pipeline as a manual upload. The per-row file uploader only
  appears as a fallback when the PDF has no embedded photo. `process_photo` is split into
  `normalize_photo` (pure image processing) and the file-loading wrapper, and a new
  `extract_embedded_photo` picks the largest raster image in the PDF by pixel area (closes #83)

tag: `intake-v0.8.0`

## [0.7.2] - 2026-08-13

### Security

- Added `.streamlit/config.toml` pinning `server.address = "127.0.0.1"`, so this tool can no
  longer accidentally bind to `0.0.0.0`/all interfaces regardless of how it's launched. This
  tool has no login layer and automates branch -> PR -> CI-poll -> squash-merge to `main` using
  the operator's own `gh`/git credentials, so "local-only" is now an enforced control instead of
  just a convention (closes #80)

tag: `intake-v0.7.2`

## [0.7.1] - 2026-08-12

### Fixed

- `parse_lay_report` was capturing only the first visual line of a title that wraps across
  multiple lines in the PDF (e.g. a long H1 spilling onto a second line), mis-filing the
  remainder as a bogus first section instead of joining it into the title (closes #76)

tag: `intake-v0.7.1`

## [0.7.0] - 2026-08-10

### Added

- Unit test suite (`pytest`, `tests/test_app.py`) for `app.py`'s pure parsing/lookup logic —
  `slugify`, `serialize_cards`, `parse_lay_report`, `guess_order_from_technical`,
  `find_technical_pdf_path`/`find_technical_ref` — alongside the tool's existing
  `streamlit.testing.v1.AppTest`/manual end-to-end verification. `order_picker`'s
  default-selection logic is extracted into a new pure `resolve_order_default_index` function so
  it's directly testable too, with no behaviour change. Gated in CI via a new required check,
  `.github/workflows/unit-dudu-intake.yml` (closes #53)

tag: `intake-v0.7.0`

## [0.6.0] - 2026-08-10

### Removed

- **Single-card "Add new card" form removed entirely** (#58) — batch add (previously an
  alternative) is now the only way to add a card, even for one at a time.

### Changed

- **Collapsed the three tabs into one page, "Card Processing"** — no more tab-switching between
  Add/Existing/Batch. One flowing page: Add cards (batch, now the sole add path), Existing cards,
  Remove multiple cards.
- **"Existing cards" now defaults collapsed**, behind a "Show existing cards" button — the full
  per-card list (photo/content/order Update, individual delete) only renders once shown.
- **"Remove multiple cards" folded into "Existing cards"** rather than staying a separate
  section: each card gets a "Select for deletion" checkbox above its own expander (checkable
  without opening the card), and one aggregate "Delete selected cards" button after the list
  publishes the whole selection as a single combined PR — same underlying logic as the old
  standalone section, just without a second duplicate list of card names to scroll through.

Validated end-to-end against the real repo twice: once for the tab consolidation, once for the
collapse/fold rework — each added then removed a throwaway species through the actual UI before
considering it done.

tag: `intake-v0.6.0`

## [0.5.0] - 2026-08-10

### Added

- **Existing cards can now have their photo, content, and order updated directly** (#52), not
  just photos alone — all three behind a **single "Update" button** per card: whichever of
  photo / lay-report-file / order was actually submitted or changed gets applied, and only
  those; the confirmation message names exactly what changed (e.g. "Updated photo, order for
  X"). Uploading a replacement lay-report PDF re-parses it (`parse_lay_report`, same as "Add new
  card") and replaces `common_name`/`sections`/`source_report_ref.lay`/`.technical` — `id`,
  `photo_ref`, `order`, and other metadata are left untouched unless also submitted in the same
  click. `reviewed_by`/`reviewed_at` are cleared on a content update, since the prior review no
  longer describes the new content. Order reuses `order_picker` from #40, defaulting to the
  card's current order or a guess from its technical report if never set. All combinations
  publish as a single "Update card: X" content-only PR, same #34 exemption as every other
  action. "Remove photo" and "Delete card permanently" remain separate, since they're
  destructive rather than update actions.

tag: `intake-v0.5.0`

## [0.4.0] - 2026-08-10

### Changed

- **Batch tab now publishes as a single combined PR** (#40), not one PR per card as first
  shipped — explicit preference after trying the per-card version: one CI run per batch instead
  of N, at the cost that one failed check now blocks every card in that batch together rather
  than isolating the failure. `do_publish`'s existing revert-on-failure behavior means a failed
  batch cleanly reverts the whole thing back to `HEAD`, not a partial mix.
- **Order dropdown now auto-detects from the technical report** when one exists locally
  (`Dudus/<CommonName>/`): every technical report states its taxonomic order in prose during
  "Taxonomy and Classification" (e.g. "Order Hemiptera") — regexed out and pre-selected. Added a
  write-in "Other (type manually)" option, pre-filled with the detected value, for orders not
  yet in the dropdown (the exact gap Stick Insects hit: Phasmatodea couldn't be selected because
  no existing card used it yet). Applies to both the single-add and batch-add flows.

tag: `intake-v0.4.0`

## [0.3.0] - 2026-08-10

### Added

- New "Batch" tab (#40): add several cards or delete several existing cards in one queued
  session instead of one at a time. Each card still publishes as its own PR (one after another,
  via the same #34 pipeline) rather than combining into a single PR — a bad PDF or a failed
  check on one card doesn't block the others, at the cost of not saving any CI wall-clock time
  over doing them individually. Duplicate ids (already live, or repeated within the same batch)
  are caught before publishing. A failed publish now reverts that card's local file changes back
  to `HEAD` (a gap that existed in the single-card flows too — fixed generally in `do_publish`,
  not just for batch), so one failure can never leave the working tree ahead of `main` in a way
  that corrupts the next action's starting point. Validated with two real throwaway species,
  added then removed as a batch, before considering it done.

tag: `intake-v0.3.0`



## [0.2.0] - 2026-08-10

### Added

- Every action (add card, delete card, save/remove photo) now automatically publishes to the
  live site (#34): commits the touched files in an isolated `git worktree`, pushes, opens a PR,
  waits for the required `e2e` check, and squash-merges if green. No VERSION/CHANGELOG/tag bump
  and no separate GitHub issue for these — content-only changes are now explicitly exempt from
  that ceremony (documented in `ONBOARDING.md`/`SKILL.md`). Failed checks leave the PR open
  rather than merging. Runs in a throwaway worktree specifically so it never disrupts whatever
  branch this working directory is actually on. Validated with two real end-to-end publishes
  (a throwaway marker file, created then removed) before wiring it into real content changes.
  Long-term direction (card data out of git entirely, for instant updates) tracked in #33.

tag: `intake-v0.2.0`

## [0.1.0] - 2026-08-09

### Added

- Initial local-only admin intake tool (Streamlit). Scoped in #15, built in #31. "Add new card"
  is exactly three inputs: a lay-report PDF (parsed with PyMuPDF into `common_name`/`id`/
  `sections`/`source_report_ref`, mirroring `md_to_pdf_rl.py`'s own H1/H2/body font styling), an
  optional photo (pad-to-3:2 + EXIF/GPS strip, mirroring `stripPhotoMetadata.ts` from #10 and
  SKILL.md's photo convention), and an order dropdown ("Unknown (fix later)" → `null`, matching
  the site's existing unclassified-order fallback). "Existing cards" lists every card with
  inline photo add/replace/remove, plus a confirm-gated permanent card delete (removes the card
  entry and its photo file together) — added after manual testing found no way back from an
  add-card mistake short of hand-editing `card_index.json`.

tag: `intake-v0.1.0`
