# Migrating posts from Substack

There are ~12 posts at [ericgitonga.substack.com](https://ericgitonga.substack.com) (2018–2025,
mostly Python/R/data pieces plus a couple of personal ones) to bring across. Do this from
Substack's own export, not by scraping the live pages — a scrape (or an AI summary of one) only
gets a paraphrase of the post, not Eric's actual words, and that's not good enough for something
going out under his name.

## 1. Export from Substack

Substack → **Settings → Exports** → request an export. It emails a ZIP containing `posts.csv`
(metadata — title, subtitle, publish date, slug — one row per post) and a `posts/` folder with
one HTML file per post.

**No images are bundled.** The exported HTML still displays images, but only because it
hotlinks back to Substack's own CDN — those URLs work today but aren't guaranteed to keep
working indefinitely once the content lives elsewhere. Download each image from its Substack URL
and save it under `blog/images/<slug>/` rather than leaving the migrated post depending on
Substack still hosting it.

## 2. Convert each post to this repo's format

For each post, create `blog/posts/<slug>.md`:

```markdown
---
title: Prompting Like A Boss!
date: 2025-05-09
excerpt: From messy process to efficient development — how Claude helped improve my prompting.
slug: prompting-like-a-boss
---

Post body in Markdown here — headings, paragraphs, lists, links, images all work
(rendered via Python's `markdown` package with the `fenced_code`/`tables`/`smarty`
extensions — see `scripts/build_blog.py`).
```

- `slug` can (and probably should) match the original Substack slug, so old links/searches
  landing on a similarly-named post still make sense.
- `excerpt` is the one-line teaser shown on `/blog` — reuse the Substack subtitle if the post
  had one, otherwise write a short one.
- `date` is the *original* publish date, not the migration date — keep the real chronology.

Images: drop them in `blog/images/<slug>/` and reference them in the Markdown as
`/blog/images/<slug>/filename.jpg`.

## 3. Rebuild

```bash
conda run -n ds python scripts/build_blog.py
```

Regenerates `blog.html` and every `blog/<slug>.html`. Commit the generated HTML alongside the
Markdown source and images — same as every other change here, through an issue + branch + PR.

## 4. Once everything's migrated

Two things worth deciding then, not now:

- Whether to point Substack's "posts" at the new URLs somehow (Substack itself doesn't support a
  redirect off its own domain — the most that's possible is a pinned note on the Substack page
  itself pointing at ericgitonga.com/blog, done manually from Substack's own dashboard).
- Whether to keep the Substack publication running at all afterward (e.g. for its email
  subscription list) or retire it once the archive is fully mirrored here.

## Offer

If Eric hands over the export ZIP, the actual per-post conversion (steps 1–2 above, for all ~12)
can be scripted rather than done by hand one at a time — ask for that when the time comes.
