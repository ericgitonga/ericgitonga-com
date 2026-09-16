# ericgitonga.com

The monorepo for [ericgitonga.com](https://ericgitonga.com) — one app per subdirectory under
`apps/`, one shared workflow (`ONBOARDING.md`), one shared version (`VERSION`/`CHANGELOG.md`).

This started as a coordination-only repo (cross-cutting issues, decisions, the shared brand kit)
while the actual code lived in three separate repos. It's now being migrated into an actual
monorepo, one app at a time. `apps/hub` went first, as a trial run: migrated with full commit
history preserved, verified building correctly from its new location, then the live
`ericgitonga.com` Vercel project was repointed at this repo (Root Directory `apps/hub`) and
promoted to production. `apps/dudus` followed the same pattern — history preserved, CI split
into app-prefixed workflows (`dudus-*`, running unconditionally rather than path-filtered — see
CHANGELOG 0.2.0 for why a required check can't be path-filtered), verified, then
`dudus.ericgitonga.com`'s Vercel project repointed and promoted. Both `eric-gitonga-links` and
`dudus-app` are archived. `dudu-merchandise` joining too is the next step, not yet done.

## Apps in this repo

| App | Where it lives | Formerly |
|---|---|---|
| `apps/hub` | `ericgitonga.com` (the hub itself) | [eric-gitonga-links](https://github.com/ericgitonga/eric-gitonga-links) (archived after migration) |
| `apps/dudus` | `dudus.ericgitonga.com` | [dudus-app](https://github.com/ericgitonga/dudus-app) (archived after migration) |

## Not yet migrated

| App | Where it lives | Repo |
|---|---|---|
| Shop | `shop.ericgitonga.com` | [dudu-merchandise](https://github.com/ericgitonga/dudu-merchandise) — independent of any one plate, not nested under Dudus |

## The hub's four cards

`ericgitonga.com` (served from `apps/hub`) is a hub page with four cards, in alphabetical order,
plus a top-level Shop link in the navbar (not one of the four cards):

| Card | Where it lives | Status |
|---|---|---|
| Daguerreotypes | `ericgitonga.com/daguerreotypes` | Real content — an album-grid gallery sourced from Angry Hosting |
| Daubs | `ericgitonga.com/daubs` | Placeholder — no real content yet |
| Diffs | `ericgitonga.com/diffs` | Real content — the Software & AI products list |
| Dudus | `dudus.ericgitonga.com` | Real, separate deployment (`apps/dudus`, this same monorepo) |

Each card name is the craft's own term of art, not a generic label: Daguerreotypes (the historic
photographic process), Daubs (the painter's own word for informal work — covers watercolours as
well as sketches), Diffs (the developer's word for comparing versions), Dudus (Kenyan slang for
insects).

Daguerreotypes/Daubs/Diffs are paths rather than real subdomains because they're just pages
within `apps/hub`, not separate deployments — see that app's own README for why a `vercel.json`
host-based rewrite was tried and abandoned in favor of paths. Dudus (and Shop) are real
subdomains because those are genuinely separate apps.

## Shared brand kit

`apps/hub`, `apps/dudus`, and `dudu-merchandise` use the same tokens — inspired by
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
hierarchy, e.g. `← ERIC GITONGA / DUDUS / SHOP` on dudu-merchandise, `← ERIC GITONGA / LEARNING`
on `apps/dudus` — IBM Plex Mono, uppercase, each ancestor a real link back up a level.
`apps/dudus` already had this brand kit fully applied (Newsreader/Archivo Narrow/IBM Plex Mono,
the same tokens) before it joined this repo — verified during migration, not something the
migration itself needed to do.

## DNS / hosting

DNS lives at AngryHosting (not delegated to Vercel — A records point at Vercel's edge instead).
Email (MX + SPF) also lives there and must never be touched by anything DNS-related here.
