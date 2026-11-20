<?php
/** @var array $posts */
?>
<style>
  .blog-page {
    max-width: 680px;
    margin: 0 auto;
    padding: clamp(2rem, 5vw, 3.5rem) 1.5rem 4rem;
    width: 100%;
  }
  h1 {
    font-family: var(--serif);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(2.2rem, 6vw, 3rem);
    line-height: 1.1;
    margin: 0 0 .5rem;
  }
  .intro {
    font-family: var(--serif);
    font-size: 1.05rem;
    color: var(--ink-muted);
    max-width: 36em;
    margin: 0 0 2.5rem;
    line-height: 1.5;
  }
  .post-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }
  .post-item {
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--line);
  }
  .post-item:last-child { border-bottom: none; }
  .post-date {
    display: block;
    font-family: var(--mono);
    font-size: .7rem;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ink-muted);
    margin-bottom: .4rem;
  }
  .post-title {
    font-family: var(--serif);
    font-style: italic;
    font-weight: 500;
    font-size: 1.6rem;
    margin: 0 0 .4rem;
  }
  .post-title a { transition: color .15s ease; }
  .post-item:hover .post-title a { color: var(--accent); }
  .post-excerpt {
    font-family: var(--serif);
    font-size: 1rem;
    color: var(--ink-muted);
    margin: 0;
    line-height: 1.5;
  }
</style>

<div class="blog-page">
  <h1>Blog</h1>
  <p class="intro">Tech and art — software, photography, drawing, watercolour, and dudus, in whatever combination a given post needs.</p>

  <ul class="post-list">
    <?php foreach ($posts as $p): ?>
      <li class="post-item">
        <span class="post-date"><?= date('d M Y', strtotime($p['date'])) ?></span>
        <h2 class="post-title"><a href="/blog/<?= htmlspecialchars($p['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?></a></h2>
        <p class="post-excerpt"><?= htmlspecialchars($p['excerpt'], ENT_QUOTES, 'UTF-8') ?></p>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
