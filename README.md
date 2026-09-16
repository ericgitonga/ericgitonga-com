# ericgitonga.com

The monorepo for [ericgitonga.com](https://ericgitonga.com) — one app per subdirectory under
`apps/`, one shared workflow (`ONBOARDING.md`), one shared version (`VERSION`/`CHANGELOG.md`).

This started as a coordination-only repo (cross-cutting issues, decisions, the shared brand kit)
while the actual code lived in three separate repos. It's now being migrated into an actual
monorepo, one app at a time — `apps/hub` first, as a trial run, before deciding whether the other
two follow.

## Apps in this repo

| App | Where it lives | Formerly |
|---|---|---|
| `apps/hub` | `ericgitonga.com` (the hub itself) | [eric-gitonga-links](https://github.com/ericgitonga/eric-gitonga-links) (archived after migration) |

## Not yet migrated

| App | Where it lives | Repo |
|---|---|---|
| Dudus | `dudus.ericgitonga.com` | [dudus-app](https://github.com/ericgitonga/dudus-app) — a real, separate deployment |
| Shop | `shop.ericgitonga.com` | [dudu-merchandise](https://github.com/ericgitonga/dudu-merchandise) — independent of any one plate, not nested under Dudus |

## The hub's four cards

`ericgitonga.com` (served from `apps/hub`) is a hub page with four cards, in alphabetical order,
plus a top-level Shop link in the navbar (not one of the four cards):

| Card | Where it lives | Status |
|---|---|---|
| Daguerreotypes | `ericgitonga.com/daguerreotypes` | Real content — an album-grid gallery sourced from Angry Hosting |
| Daubs | `ericgitonga.com/daubs` | Placeholder — no real content yet |
| Diffs | `ericgitonga.com/diffs` | Real content — the Software & AI products list |
| Dudus | `dudus.ericgitonga.com` | Real, separate deployment (`dudus-app`) |

Each card name is the craft's own term of art, not a generic label: Daguerreotypes (the historic
photographic process), Daubs (the painter's own word for informal work — covers watercolours as
well as sketches), Diffs (the developer's word for comparing versions), Dudus (Kenyan slang for
insects).

Daguerreotypes/Daubs/Diffs are paths rather than real subdomains because they're just pages
within `apps/hub`, not separate deployments — see that app's own README for why a `vercel.json`
host-based rewrite was tried and abandoned in favor of paths. Dudus (and Shop) are real
subdomains because those are genuinely separate apps.

## Shared brand kit

`apps/hub` and `dudu-merchandise` use the same tokens — inspired by
[danwintersphoto.com](https://www.danwintersphoto.com)'s minimal, image-first gallery aesthetic:

```css
--ground: #f3efe3;        /* page background */
--ground-raised: #ece4cf; /* card/raised surface */
--ink: #241f16;           /* primary text */
--ink-muted: #7a7160;     /* secondary text */
--accent: #3f6b53;        /* one accent color — verdigris */
--accent-dim: #8ba38f;    /* dimmer accent (resting-state borders) */
--line: #d9cfb8;          /* hairline borders/dividers */
```

Fonts: **Newsreader** (italic display serif, headings), **Archivo Narrow** (default UI sans),
**IBM Plex Mono** (numbers, labels, the standardized breadcrumb).

Every page also carries a standardized top-left breadcrumb reflecting its real place in the
hierarchy, e.g. `← ERIC GITONGA / DUDUS / SHOP` on dudu-merchandise — IBM Plex Mono, uppercase,
each ancestor a real link back up a level. **`dudus-app` does not share these tokens** — it uses
Next.js's default Geist/Geist Mono fonts and has no shared CSS custom properties, despite being
part of the same family; bringing it in line is a separate design decision from the monorepo
migration itself (see `extras/personal/me/unified.pdf`, outside this repo).

## DNS / hosting

DNS lives at AngryHosting (not delegated to Vercel — A records point at Vercel's edge instead).
Email (MX + SPF) also lives there and must never be touched by anything DNS-related here.
