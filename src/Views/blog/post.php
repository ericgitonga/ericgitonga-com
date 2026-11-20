<?php
/** @var array $post */
?>
<style>
  .post-page {
    max-width: 680px;
    margin: 0 auto;
    padding: clamp(2rem, 5vw, 3.5rem) 1.5rem 4rem;
    width: 100%;
  }
  .post-meta {
    font-family: var(--mono);
    font-size: .72rem;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ink-muted);
    margin-bottom: .6rem;
  }
  h1 {
    font-family: var(--serif);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(2.2rem, 6vw, 3.2rem);
    line-height: 1.1;
    margin: 0 0 1.5rem;
    text-wrap: balance;
  }
  .post-body {
    font-family: var(--serif);
    font-size: 1.12rem;
    line-height: 1.65;
    color: var(--ink);
  }
  .post-body p { margin: 0 0 1.25rem; }
  .post-body pre {
    background: var(--ground-raised);
    border: 1px solid var(--line);
    padding: 1rem;
    overflow-x: auto;
    font-family: var(--mono);
    font-size: .88rem;
  }
  .post-body code {
    font-family: var(--mono);
    font-size: .9em;
    background: var(--ground-raised);
    padding: .1em .3em;
  }
  .post-body pre code { background: none; padding: 0; }
  .back-link {
    display: inline-block;
    margin-top: 3rem;
    font-family: var(--mono);
    font-size: .78rem;
    text-transform: uppercase;
    color: var(--accent);
    border-bottom: 1px solid var(--accent);
    padding-bottom: .2rem;
  }
</style>

<div class="post-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/blog">← BLOG</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page"><?= htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8') ?></span>
  </div>

  <p class="post-meta"><?= date('d M Y', strtotime($post['date'])) ?></p>
  <h1><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h1>

  <div class="post-body">
    <?= $post['body_html'] ?>
  </div>

  <a class="back-link" href="/blog">← Back to Blog</a>
</div>
