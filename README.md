# ericgitonga.com

This repo has no code. It exists to coordinate work that spans the repos making up
[ericgitonga.com](https://ericgitonga.com) — cross-cutting issues, decisions, and the shared
brand kit all live here instead of arbitrarily attaching to whichever repo happened to be first.

## The family

`ericgitonga.com` is a hub page with four cards, in alphabetical order. Each links to its own
ecosystem — some are real separate apps/repos, some are just pages on the hub itself:

| Card | Where it lives | Repo |
|---|---|---|
| Daguerreotypes | `ericgitonga.com/daguerreotypes` | page in [eric-gitonga-links](https://github.com/ericgitonga/eric-gitonga-links) — placeholder |
| Daubs | `ericgitonga.com/daubs` | page in [eric-gitonga-links](https://github.com/ericgitonga/eric-gitonga-links) — placeholder |
| Diffs | `ericgitonga.com/diffs` | page in [eric-gitonga-links](https://github.com/ericgitonga/eric-gitonga-links) — real content |
| Dudus | `dudus.ericgitonga.com` | [dudus-app](https://github.com/ericgitonga/dudus-app) — a real, separate deployment |
| ↳ Shop | `shop.dudus.ericgitonga.com` | [dudu-merchandise](https://github.com/ericgitonga/dudu-merchandise) — nested under Dudus, not a top-level card |

Each card name is the craft's own term of art, not a generic label: Daguerreotypes (the historic
photographic process), Daubs (the painter's own word for informal work — covers watercolours as
well as sketches), Diffs (the developer's word for comparing versions), Dudus (Kenyan slang for
insects).

Daguerreotypes/Daubs/Diffs are paths rather than real subdomains because they're just pages within
eric-gitonga-links, not separate deployments — see that repo's README for why a `vercel.json`
host-based rewrite was tried and abandoned in favor of paths. Dudus and its nested Shop are real
subdomains because those are genuinely separate apps.

## Shared brand kit

All three real codebases (eric-gitonga-links, dudus-app, dudu-merchandise) use the same tokens —
inspired by [danwintersphoto.com](https://www.danwintersphoto.com)'s minimal, image-first gallery
aesthetic:

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
each ancestor a real link back up a level.

## DNS / hosting

DNS lives at AngryHosting (not delegated to Vercel — A records point at Vercel's edge instead).
Email (MX + SPF) also lives there and must never be touched by anything DNS-related here.
