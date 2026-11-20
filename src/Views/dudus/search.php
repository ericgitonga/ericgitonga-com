<?php
/** @var array $results */
$q = (string)($_GET['q'] ?? '');
?>
<style>
  .plate-page {
    max-width: 960px;
    margin: 0 auto;
    padding: clamp(2rem, 5vw, 3.5rem) 1.5rem 4rem;
    width: 100%;
  }
  h1 {
    font-family: var(--serif);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(2.4rem, 6vw, 3.2rem);
    line-height: 1.05;
    margin: 0 0 .7rem;
  }
  .search-form {
    display: flex;
    gap: .8rem;
    margin: 1.5rem 0 2rem;
  }
  .search-form input {
    flex: 1;
    padding: .6rem .8rem;
    border: 1px solid var(--line);
    background: var(--ground-raised);
    font-family: var(--label);
    font-size: 1rem;
    color: var(--ink);
  }
  .search-form button {
    padding: .6rem 1.4rem;
    background: var(--accent);
    color: var(--ground);
    border: none;
    font-family: var(--mono);
    font-size: .78rem;
    text-transform: uppercase;
    cursor: pointer;
  }
  .results-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  .result-item {
    border: 1px solid var(--line);
    background: var(--ground-raised);
    padding: 1.2rem;
    transition: border-color .15s ease;
  }
  .result-item:hover { border-color: var(--accent); }
  .result-title {
    font-family: var(--serif);
    font-size: 1.3rem;
    font-style: italic;
    margin: 0 0 .2rem;
  }
  .result-meta {
    font-family: var(--mono);
    font-size: .72rem;
    color: var(--ink-muted);
    margin-bottom: .4rem;
  }
  .result-blurb {
    font-family: var(--serif);
    font-size: 1rem;
    color: var(--ink);
    margin: 0;
    line-height: 1.45;
  }
</style>

<div class="plate-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/dudus">← DUDUS</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page">SEARCH</span>
  </div>

  <h1>Search Dudus</h1>

  <form class="search-form" method="GET" action="/dudus/search">
    <input type="text" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search species..." required>
    <button type="submit">Search</button>
  </form>

  <p style="font-family: var(--mono); font-size: .8rem; color: var(--ink-muted); margin-bottom: 1.5rem;">
    Found <?= count($results) ?> species matching "<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>".
  </p>

  <ul class="results-list">
    <?php foreach ($results as $r): ?>
      <li class="result-item">
        <a href="/dudus/card/<?= urlencode((string)$r['id']) ?>">
          <h2 class="result-title"><?= htmlspecialchars((string)$r['common_name'], ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="result-meta">Order: <?= htmlspecialchars((string)$r['order'], ENT_QUOTES, 'UTF-8') ?> · Family: <?= htmlspecialchars((string)$r['family'], ENT_QUOTES, 'UTF-8') ?></div>
          <p class="result-blurb"><?= htmlspecialchars((string)$r['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
