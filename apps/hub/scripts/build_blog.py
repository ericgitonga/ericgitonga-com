#!/usr/bin/env python3
"""Generate blog.html and blog/<slug>.html from blog/posts/*.md.

Run locally (conda env `ds`) after adding/editing a post, then commit the
generated HTML alongside the Markdown source — this repo has no build step
on Vercel, so the generated files are what actually gets deployed.

    conda run -n ds python scripts/build_blog.py
"""
from __future__ import annotations

import datetime as dt
import re
from pathlib import Path

import markdown
import yaml

ROOT = Path(__file__).resolve().parent.parent
POSTS_DIR = ROOT / "blog" / "posts"
OUT_DIR = ROOT / "blog"
INDEX_PATH = ROOT / "blog.html"

FRONTMATTER_RE = re.compile(r"\A---\n(.*?)\n---\n(.*)\Z", re.DOTALL)

FONT_LINKS = (
    '<meta name="viewport" content="width=device-width, initial-scale=1">\n'
    '<link rel="icon" href="/favicon.ico" sizes="any">\n'
    '<link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">\n'
    '<link rel="icon" href="/favicon-16x16.png" type="image/png" sizes="16x16">\n'
    '<link rel="apple-touch-icon" href="/apple-touch-icon.png">\n'
    '<link rel="preconnect" href="https://fonts.googleapis.com">\n'
    '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>\n'
    '<link href="https://fonts.googleapis.com/css2?family=Archivo+Narrow:ital,wght@0,500;0,600;1,500'
    '&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400;1,6..72,500'
    '&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">'
)

SHARED_CSS = """
  :root{
    --ground:#f3efe3;
    --ground-raised:#ece4cf;
    --ink:#241f16;
    --ink-muted:#7a7160;
    --accent:#3f6b53;
    --accent-dim:#8ba38f;
    --line:#d9cfb8;
    --serif:'Newsreader', Georgia, 'Times New Roman', serif;
    --label:'Archivo Narrow', 'Arial Narrow', sans-serif;
    --mono:'IBM Plex Mono', ui-monospace, 'SFMono-Regular', Menlo, monospace;
  }
  *{box-sizing:border-box;}
  body{
    margin:0;
    background:var(--ground);
    color:var(--ink);
    font-family:var(--label);
    line-height:1.5;
    -webkit-font-smoothing:antialiased;
  }
  a{color:inherit;text-decoration:none;}
  @media (prefers-reduced-motion:reduce){
    *{animation-duration:.01ms !important;transition-duration:.01ms !important;}
  }
  a:focus-visible, button:focus-visible{
    outline:2px solid var(--accent);
    outline-offset:3px;
  }
  .topbar{
    display:flex;
    align-items:baseline;
    justify-content:space-between;
    padding:1.3rem 1.8rem;
    border-bottom:1px solid var(--line);
    flex-wrap:wrap;
    gap:.4rem;
    position:sticky;
    top:0;
    z-index:10;
    background:var(--ground);
  }
  .breadcrumb{
    display:flex;
    align-items:baseline;
    gap:.5rem;
    font-family:var(--mono);
    font-size:.78rem;
    font-weight:500;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:var(--ink);
  }
  .breadcrumb a{color:var(--ink);transition:color .15s ease;}
  .breadcrumb a:hover, .breadcrumb a:focus-visible{color:var(--accent);}
  .crumb-sep{color:var(--ink-muted); font-weight:400;}
  .breadcrumb [aria-current="page"]{color:var(--ink);}
  .page{
    max-width:680px;
    margin:0 auto;
    padding:clamp(2.5rem,6vw,4rem) clamp(1.25rem,4vw,1.5rem) 4rem;
  }
  h1{
    font-family:var(--serif);
    font-style:italic;
    font-weight:500;
    font-size:clamp(2.2rem,6vw,3rem);
    line-height:1.1;
    margin:0 0 .5rem;
    text-wrap:balance;
  }
  .intro{
    font-family:var(--serif);
    font-size:1.05rem;
    color:var(--ink-muted);
    max-width:36em;
    margin:0 0 2.5rem;
    line-height:1.5;
  }
  .foot{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:.9rem 1.8rem;
    border-top:1px solid var(--line);
    font-family:var(--mono);
    font-size:.68rem;
    letter-spacing:.08em;
    color:var(--ink-muted);
  }
"""

INDEX_CSS = """
  .post-list{
    list-style:none;
    margin:0;
    padding:0;
    display:flex;
    flex-direction:column;
    gap:1.4rem;
  }
  .post-item{
    padding-bottom:1.4rem;
    border-bottom:1px solid var(--line);
  }
  .post-item:last-child{border-bottom:none;}
  .post-date{
    display:block;
    font-family:var(--mono);
    font-size:.7rem;
    letter-spacing:.1em;
    text-transform:uppercase;
    color:var(--ink-muted);
    margin-bottom:.4rem;
  }
  .post-title{
    font-family:var(--serif);
    font-style:italic;
    font-weight:500;
    font-size:1.5rem;
    margin:0 0 .4rem;
  }
  .post-title a{transition:color .15s ease;}
  .post-item:hover .post-title a, .post-title a:focus-visible{color:var(--accent);}
  .post-excerpt{
    font-family:var(--serif);
    font-size:.98rem;
    color:var(--ink-muted);
    margin:0;
    line-height:1.5;
  }
"""

POST_CSS = """
  .post-meta{
    font-family:var(--mono);
    font-size:.72rem;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:var(--ink-muted);
    margin:0 0 2rem;
  }
  .post-body{
    font-family:var(--serif);
    font-size:1.08rem;
    line-height:1.65;
  }
  .post-body p{margin:0 0 1.2rem;}
  .post-body h2{
    font-family:var(--serif);
    font-style:italic;
    font-weight:500;
    font-size:1.5rem;
    margin:2rem 0 .8rem;
  }
  .post-body h3{
    font-family:var(--label);
    font-weight:600;
    font-size:.95rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    color:var(--ink-muted);
    margin:1.8rem 0 .6rem;
  }
  .post-body ul, .post-body ol{margin:0 0 1.2rem; padding-left:1.4rem;}
  .post-body li{margin-bottom:.4rem;}
  .post-body a{color:var(--accent); border-bottom:1px solid var(--accent-dim);}
  .post-body a:hover, .post-body a:focus-visible{border-color:var(--accent);}
  .post-body img{max-width:100%; height:auto; margin:1.5rem 0;}
  .post-body pre{
    background:var(--ground-raised);
    border:1px solid var(--line);
    padding:1rem;
    overflow-x:auto;
    font-family:var(--mono);
    font-size:.85rem;
  }
  .post-body code{
    font-family:var(--mono);
    font-size:.9em;
    background:var(--ground-raised);
    padding:.1em .3em;
  }
  .post-body pre code{background:none; padding:0;}
  .post-body blockquote{
    margin:0 0 1.2rem;
    padding-left:1rem;
    border-left:3px solid var(--accent-dim);
    color:var(--ink-muted);
  }
  .back-to-blog{
    display:inline-block;
    margin-top:2.5rem;
    font-family:var(--mono);
    font-size:.78rem;
    letter-spacing:.02em;
    color:var(--accent);
    border-bottom:1px solid var(--accent-dim);
    padding-bottom:.2rem;
  }
  .back-to-blog:hover, .back-to-blog:focus-visible{border-color:var(--accent);}
"""

MD = markdown.Markdown(extensions=["fenced_code", "tables", "smarty"])


def load_post(path: Path) -> dict:
    text = path.read_text(encoding="utf-8")
    m = FRONTMATTER_RE.match(text)
    if not m:
        raise ValueError(f"{path}: missing YAML frontmatter (--- ... ---)")
    meta = yaml.safe_load(m.group(1)) or {}
    body_md = m.group(2).strip()
    MD.reset()
    body_html = MD.convert(body_md)

    required = ("title", "date", "excerpt")
    missing = [k for k in required if k not in meta]
    if missing:
        raise ValueError(f"{path}: frontmatter missing {missing}")

    slug = meta.get("slug") or path.stem
    date_val = meta["date"]
    if isinstance(date_val, str):
        date_val = dt.date.fromisoformat(date_val)

    return {
        "slug": slug,
        "title": str(meta["title"]),
        "excerpt": str(meta["excerpt"]),
        "date": date_val,
        "body_html": body_html,
        "source": path.name,
    }


def page_shell(title: str, description: str, breadcrumb: str, extra_css: str, body: str) -> str:
    return f"""<title>{title} — Eric Gitonga</title>
{FONT_LINKS}
<meta name="description" content="{description}">

<style>
{SHARED_CSS}
{extra_css}
</style>

<header class="topbar">
  <nav class="breadcrumb" aria-label="Breadcrumb">
{breadcrumb}
  </nav>
</header>

{body}

<footer class="foot">
  <span>ERIC GITONGA</span>
  <span>ericgitonga.com/blog</span>
</footer>
"""


def render_index(posts: list[dict]) -> str:
    breadcrumb = (
        '    <a href="/">← ERIC GITONGA</a>\n'
        '    <span class="crumb-sep" aria-hidden="true">/</span>\n'
        '    <span aria-current="page">BLOG</span>'
    )
    items = []
    for post in posts:
        items.append(
            '      <li class="post-item">\n'
            f'        <span class="post-date">{post["date"]:%d %b %Y}</span>\n'
            f'        <h2 class="post-title"><a href="/blog/{post["slug"]}">{post["title"]}</a></h2>\n'
            f'        <p class="post-excerpt">{post["excerpt"]}</p>\n'
            "      </li>"
        )
    body = (
        '<div class="page">\n'
        "  <h1>Blog</h1>\n"
        '  <p class="intro">Tech and art — software, photography, drawing, watercolour, and dudus,'
        " in whatever combination a given post needs.</p>\n"
        '  <ul class="post-list">\n' + "\n".join(items) + "\n  </ul>\n"
        "</div>"
    )
    return page_shell(
        "Blog",
        "Tech and art — software, photography, drawing, watercolour, and dudus.",
        breadcrumb,
        INDEX_CSS,
        body,
    )


def render_post(post: dict) -> str:
    breadcrumb = (
        '    <a href="/">← ERIC GITONGA</a>\n'
        '    <span class="crumb-sep" aria-hidden="true">/</span>\n'
        '    <a href="/blog">BLOG</a>'
    )
    body = (
        '<div class="page">\n'
        f'  <p class="post-meta">{post["date"]:%d %b %Y}</p>\n'
        f'  <h1>{post["title"]}</h1>\n'
        f'  <div class="post-body">\n{post["body_html"]}\n  </div>\n'
        '  <a class="back-to-blog" href="/blog">← Back to Blog</a>\n'
        "</div>"
    )
    return page_shell(post["title"], post["excerpt"], breadcrumb, POST_CSS, body)


def main() -> None:
    OUT_DIR.mkdir(exist_ok=True)
    md_files = sorted(POSTS_DIR.glob("*.md"))
    if not md_files:
        raise SystemExit(f"No posts found in {POSTS_DIR}")

    posts = [load_post(p) for p in md_files]
    posts.sort(key=lambda p: p["date"], reverse=True)

    slugs = [p["slug"] for p in posts]
    dupes = {s for s in slugs if slugs.count(s) > 1}
    if dupes:
        raise SystemExit(f"Duplicate slugs: {dupes}")

    INDEX_PATH.write_text(render_index(posts), encoding="utf-8")
    print(f"wrote {INDEX_PATH.relative_to(ROOT)}")

    for post in posts:
        out_path = OUT_DIR / f"{post['slug']}.html"
        out_path.write_text(render_post(post), encoding="utf-8")
        print(f"wrote {out_path.relative_to(ROOT)}  (from {post['source']})")

    print(f"\n{len(posts)} post(s) built.")


if __name__ == "__main__":
    main()
