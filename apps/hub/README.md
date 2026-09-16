# eric-gitonga-links

The hub for [ericgitonga.com](https://www.ericgitonga.com/) — four cards, in alphabetical
order:

| Card | Where it lives | What it is |
|---|---|---|
| Daguerreotypes | `ericgitonga.com/daguerreotypes` | Photography — landscapes, portraits, documentary moments. Live content (`daguerreotypes.html`): an album-grid gallery sourced from Angry Hosting (see Media section below). |
| Daubs | `ericgitonga.com/daubs` | Drawings, watercolours, and marks made for their own sake. Placeholder page (`daubs.html`) for now. |
| Diffs | `ericgitonga.com/diffs` | Software and systems — Eric's technical practice. Live content (`diffs.html`): the Software & AI products list. |
| Dudus | `ericgitonga.com/dudus` | Kenya's tiniest wildlife. This page (`dudus.html`) is two image-led cards, both opening in the same window: Dutraits (`/dudus/dutraits`, the album-grid gallery of Eric's own dudu photographs) and Dudepth ([dudus-app](https://dudus.ericgitonga.com), the identification companion). A third card, Dumerch, linked to the shop from here until the shop became independent of any one plate — it's reachable via the navbar's own Shop link now instead. |

Card names are each a term of art specific to their craft, not a generic label —
Daguerreotypes (the historic photographic process), Daubs (the painter's own word for informal
work), Diffs (the developer's word for comparing versions), Dudus (Kenyan slang for insects).

Every card is a path on this one repo. Dudus is the only one whose landing page is itself just a
switchboard — its Dutraits card leads to a nested page in this same repo (the album gallery),
while Dudepth links onward to its own real external deployment.

Daguerreotypes, Daubs, and Diffs are paths on this one static site, not subdomains — they're just
pages in this repo, not separate deployments, so there's no real infrastructure behind a
`daguerreotypes.ericgitonga.com`-style hostname yet. (An earlier draft tried host-based
`vercel.json` rewrites to fake real subdomains for these three; that mechanism is
[documented as unreliable in production](https://community.vercel.com/t/vercel-json-the-has-condition-on-host-doesnt-seem-to-work/9863),
and it didn't fire in local testing either, so it was dropped in favor of plain paths — reliable,
and honest about what's actually deployed.) If any of them grows into its own real app someday,
it can graduate to a real subdomain then, the same way dudus-app and dudu-merchandise already
have — those are genuinely separate deployments, just reached via a link from `/dudus` rather
than directly from the hub card.

Static HTML, no build step, deployed straight to Vercel. `vercel.json` sets `cleanUrls: true`
so `/diffs` serves `diffs.html` (etc.) without the extension — no framework/router needed.

Every page shares one navbar layout: "ERIC GITONGA" (linked to `/`, except on the hub itself)
on the left, the four plates centred (the current page's own plate shown as plain text, not a
link), and Bio/Blog/Shop/Contact on the right. Shop links out to `shop.ericgitonga.com`
(dudu-merchandise — independent of any one plate, per that repo's own issue #119) rather than a
path on this site. Bio and Contact are modal dialogs — their markup, styles, and the Contact
form's logic only exist on `index.html`; every other page's Bio/Contact links point at
`/#bio-modal` / `/#contact-modal`, and a small script on `index.html` opens the matching dialog
on load if that hash is present (and clears it again when the dialog closes), rather than
duplicating the modal machinery onto all seven pages. Blog is a real page (`/blog`). The Contact
modal's form POSTs to `/api/contact` (a Vercel serverless function using Resend's REST API
directly, no SDK) instead of a `mailto:` link, so a visitor's message reaches `gitonga@gmail.com`
without them needing their own mail client open — requires `RESEND_API_KEY` (and optionally
`FROM_EMAIL`) set as a Vercel env var on this project.

## Blog

`/blog` and `/blog/<slug>` — same brand kit and breadcrumb as the rest of the site, so "back to
ericgitonga.com" actually works (this replaced an external link to a Substack blog for exactly
that reason: no way back into the rest of the site from there).

No CMS. Posts are Markdown with YAML frontmatter in `blog/posts/*.md`, rendered to static HTML
by `scripts/build_blog.py` (Python's `markdown` + `yaml`, both already in the `ds` conda env — no
new dependency). Run it locally after adding/editing a post and commit the generated
`blog.html`/`blog/<slug>.html` alongside the source, same as everything else here:

```bash
conda run -n ds python scripts/build_blog.py
```

See `blog/MIGRATION.md` for bringing the existing Substack archive (~12 posts, 2018–2025) across.

Design: a shared brand kit across `eric-gitonga-links`, `dudus-app`, and `dudu-merchandise` —
warm paper-cream ground, dark ink, one verdigris accent, Newsreader/Archivo Narrow/IBM Plex Mono
type pairing, and catalogue-plate numbering (PLATE I–IV). Reference:
`extras/personal/me/eric-hub-concept.html` in the wider `Develop/projects` tree (not part of
this repo). The top-left `← ERIC GITONGA / <PAGE>` breadcrumb this repo used to share with
`dudus-app`/`dudu-merchandise` was replaced here by the three-column navbar described above
(Eric Gitonga / four plates / Bio-Blog-Contact) — the other two sites still use the breadcrumb,
so that piece of the shared brand kit has diverged for this repo specifically.

## Media (Daguerreotypes / Dudus galleries)

Photo/entomology media for the Daguerreotypes and Dudus plates is sourced from Eric's Angry
Hosting account, not this repo — uploading a new photo there is enough to make it appear on the
site, no code change or redeploy needed. Folder convention on Angry Hosting (one level of albums,
no further nesting): `Daguerreotypes/<album>/<image>` and `Dudus/<album>/<image>`, hyphenated
album slugs, an optional `cover.<ext>` file per album to set its grid thumbnail (defaults to the
first image alphabetically otherwise).

`api/sync-media.js` is a Vercel Serverless Function, triggered once daily by Vercel Cron
(`vercel.json`), that connects to Angry Hosting over FTP(S) (`basic-ftp`), walks both plates'
albums, and publishes a JSON manifest to Vercel Blob (`@vercel/blob`) — this is the first
`package.json` this repo has needed (the contact form calls Resend's REST API directly with
`fetch`, no dependency). `api/media-manifest.js` is a thin same-origin proxy the static gallery
pages read from, so client code never needs to know the actual Blob URL.

`assets/gallery.js` is a shared script (the one exception to this repo's usual per-page
duplication — real interactive logic, not boilerplate) that both `daguerreotypes.html` and
`dudus/dutraits.html` load. It fetches `/api/media-manifest`, renders an album grid, and opens a
`<dialog>` modal image viewer (previous/next arrows, ←/→ keyboard nav, click-outside/Esc to close,
a fullscreen toggle via the Fullscreen API, and a thumbnail strip along the bottom to jump
directly to a specific photo) when an album is chosen — same modal pattern as the hub page's
Bio/Contact dialogs. The fullscreen toggle targets the `<figure>` inside the dialog, not the
`<dialog>` itself — Chromium rejects `requestFullscreen()` called directly on a `<dialog>` opened
via `showModal()` ("Dialog elements are invalid"), and every close/nav/thumbnail control lives
inside that same `<figure>` so it stays usable once fullscreen is entered, rather than being
visually stranded behind it. Each page calls
`initGallery('daguerreotypes')` or `initGallery('dudus')` to wire itself up (the manifest key is
still `dudus` — only the page it renders on moved, not the plate it belongs to).

Requires these Vercel env vars: `ANGRYHOSTING_FTP_HOST`, `ANGRYHOSTING_FTP_USER`,
`ANGRYHOSTING_FTP_PASSWORD`, `MEDIA_BASE_URL` (currently `http://media.ericgitonga.com` — **switch
to `https://` the moment Angry Hosting issues a certificate for that subdomain**, env var change
only), `CRON_SECRET` (so `/api/sync-media` can't be triggered by anyone else). Optional:
`ANGRYHOSTING_FTP_BASE_PATH` if the FTP account's login root isn't already the
`media.ericgitonga.com` webroot.

## Updating

See `ONBOARDING.md` — issue first, then branch + PR, same as every other repo in this family.
`main` is branch-protected; Vercel redeploys on push to `main` once a PR merges.
