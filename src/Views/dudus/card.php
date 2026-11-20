<?php
/** @var array $card */
?>
<style>
  .card-page {
    max-width: 820px;
    margin: 0 auto;
    padding: clamp(2rem, 5vw, 3.5rem) 1.5rem 4rem;
    width: 100%;
  }
  .card-header-meta {
    font-family: var(--mono);
    font-size: .72rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--ink-muted);
    margin-bottom: .5rem;
  }
  h1 {
    font-family: var(--serif);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(2.2rem, 6vw, 3.2rem);
    line-height: 1.1;
    margin: 0 0 .5rem;
  }
  .sci-name {
    font-family: var(--serif);
    font-size: 1.1rem;
    color: var(--accent);
    margin-bottom: 1.5rem;
  }
  .photo-frame {
    width: 100%;
    max-height: 520px;
    border: 1px solid var(--line);
    background: #000;
    overflow: hidden;
    margin-bottom: 2rem;
  }
  .photo-frame img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
  }
  .meta-table {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    padding: 1.2rem;
    border: 1px solid var(--line);
    background: var(--ground-raised);
    margin-bottom: 2rem;
  }
  .meta-item span {
    display: block;
    font-family: var(--mono);
    font-size: .68rem;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ink-muted);
    margin-bottom: .2rem;
  }
  .meta-item strong {
    font-family: var(--serif);
    font-size: 1.05rem;
    font-weight: 500;
    color: var(--ink);
  }
  .blurb-text {
    font-family: var(--serif);
    font-size: 1.15rem;
    line-height: 1.6;
    color: var(--ink);
  }
  @media (max-width: 600px) {
    .meta-table { grid-template-columns: 1fr; }
  }
</style>

<div class="card-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/">← ERIC GITONGA</a>
    <span class="crumb-sep">/</span>
    <a href="/dudus">DUDUS</a>
    <span class="crumb-sep">/</span>
    <a href="/dudus/orders/<?= urlencode((string)$card['order']) ?>"><?= htmlspecialchars((string)$card['order'], ENT_QUOTES, 'UTF-8') ?></a>
    <span class="crumb-sep">/</span>
    <span aria-current="page"><?= htmlspecialchars((string)$card['id'], ENT_QUOTES, 'UTF-8') ?></span>
  </div>

  <p class="card-header-meta">Order: <?= htmlspecialchars((string)$card['order'], ENT_QUOTES, 'UTF-8') ?> · Family: <?= htmlspecialchars((string)$card['family'], ENT_QUOTES, 'UTF-8') ?></p>
  <h1><?= htmlspecialchars((string)$card['common_name'], ENT_QUOTES, 'UTF-8') ?></h1>
  <?php if (!empty($card['scientific_name'])): ?>
    <div class="sci-name"><?= htmlspecialchars((string)$card['scientific_name'], ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if (!empty($card['image_url'])): ?>
    <div class="photo-frame">
      <img src="<?= htmlspecialchars((string)$card['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$card['common_name'], ENT_QUOTES, 'UTF-8') ?>">
    </div>
  <?php endif; ?>

  <div class="meta-table">
    <div class="meta-item">
      <span>Typical Size</span>
      <strong><?= htmlspecialchars((string)($card['size'] ?: 'Varies'), ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
    <div class="meta-item">
      <span>Habitat</span>
      <strong><?= htmlspecialchars((string)($card['habitat'] ?: 'Kenya'), ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
    <div class="meta-item">
      <span>Diet / Role</span>
      <strong><?= htmlspecialchars((string)($card['diet'] ?: 'Ecological'), ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
  </div>

  <div class="blurb-text">
    <p><?= nl2br(htmlspecialchars((string)$card['blurb'], ENT_QUOTES, 'UTF-8')) ?></p>
  </div>
</div>
