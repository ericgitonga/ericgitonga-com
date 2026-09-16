# dudu intake tool

Local-only admin tool for adding, updating, and deleting cards in `src/data/card_index.json` and
their photos. Scoped in #15, built in #31/#32, connected to the live site in #34, consolidated
into a single "Card Processing" page in #58.

**Not part of the deployed app.** Nothing under `tools/` is imported by the Next.js app or
referenced by its build config — this only runs when you start it yourself, on your own
machine.

## Run it

From the repo root:

```bash
conda run -n ds streamlit run tools/dudu-intake/app.py
```

Dependencies (`streamlit`, `pillow`, `pymupdf`) live in the `ds` conda environment — install with
`conda run -n ds pip install -r tools/dudu-intake/requirements.txt` if they're ever missing.

## What it does

One page, "Card Processing" (#58) — no tabs, no single-card add form. Three sections top to
bottom:

- **Add cards** (#40) — multi-file lay-report upload, the *only* way to add a card, even one at
  a time. Each PDF is parsed independently with PyMuPDF: the report's own title becomes
  `common_name`/`id`, each bold heading starts a new section, and the regular-weight text under
  it becomes that section's body — mirroring exactly how `md_to_pdf_rl.py` styles H1/H2/body when
  it originally generated the PDF. A leading "The/A/An" is stripped from the title. Each row's
  photo is taken automatically from whatever the report itself embeds under its taxonomy line
  (SKILL.md's Photo line convention) — run through the same metadata-strip + 3:2-pad pipeline as
  a manual upload (#83); only when the PDF has no embedded photo does the row fall back to its
  own file uploader. Each row also gets an order picker: a dropdown of orders already in use,
  plus "Unknown (fix later)" → `null`, plus a write-in "Other (type manually)" for a genuinely new
  order — every technical report states its order in prose ("Order X" in its Taxonomy and
  Classification section), auto-detected from the sibling technical PDF in `Dudus/<CommonName>/`
  when present, pre-selecting or pre-filling accordingly. Duplicate ids (already live, or repeated
  within the batch) are caught before publishing. The whole batch publishes as **one combined
  PR** — one CI run instead of one per card, at the cost that a failed check blocks every card in
  that batch together. `scientific_name`, `family`, `taxon_rank` (defaults `"species"`), and
  `sourcing` (left blank) have no edit UI yet, so they land as clear placeholders, not guesses.
- **Existing cards** (#52, #58) — defaults **collapsed**, behind a "Show existing cards" button
  (the reverse toggles it back to "Hide"). Once shown: every card gets a "Select for deletion"
  checkbox above its own expander (checkable without opening the card) plus, inside the
  expander, a photo uploader, a lay-report-replace uploader, and an order picker all behind
  **one "Update" button** — submit any combination of the three and only that combination is
  applied, as a single "Update card: X" PR. Replacing the lay report re-parses it the same way
  as adding, leaving `id`/`photo_ref`/`order`/other metadata untouched unless also changed in the
  same click, and clears `reviewed_by`/`reviewed_at` since the old review no longer describes new
  content. "Remove photo" and "Delete card permanently" stay as their own separate, confirm-gated
  destructive actions, not folded into "Update". After the full card list, checking any
  "Select for deletion" boxes reveals **one aggregate "Delete selected cards" button** — no
  separate "Remove multiple cards" section anymore, this replaces it entirely.

## Publishing to the live site

Every action (add card, delete card, save/remove photo) edits `src/data/card_index.json` /
`public/photos/` in your working tree for instant local feedback, then automatically publishes:
it creates a real branch, commits just the files that action touched, pushes, opens a PR, waits
for the required `e2e` check, and squash-merges if green — no VERSION/CHANGELOG/tag bump and no
separate GitHub issue for this, since it's content rather than a code release (see
`ONBOARDING.md`'s "automated content publishing" exception). If checks fail, the PR is left open
instead of merged, with the failure shown in the tool.

This runs entirely in a throwaway `git worktree`, never by checking out a different branch in
this working directory — so it can't disrupt whatever this checkout is currently doing (e.g. a
concurrent dev session working on the tool's own code), and it's safe to use even while another
change is mid-publish. Each publish takes roughly as long as CI does (a minute or two), not
instant — the eventual instant-update version (card data out of git entirely) is tracked
separately in #33.
