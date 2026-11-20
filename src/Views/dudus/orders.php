<?php
/** @var array $cards */
$orderName = $cards[0]['order'] ?? 'Order';
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
  .species-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.25rem;
    margin-top: 2rem;
  }
  .species-card {
    border: 1px solid var(--line);
    background: var(--ground-raised);
    display: flex;
    flex-direction: column;
    transition: border-color .15s ease;
  }
  .species-card:hover { border-color: var(--accent); }
  .species-thumb {
    width: 100%;
    aspect-ratio: 4/3;
    object-fit: cover;
    background: #000;
  }
  .species-info {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: .25rem;
  }
  .species-title {
    font-family: var(--serif);
    font-style: italic;
    font-size: 1.2rem;
    font-weight: 500;
    margin: 0;
  }
  .species-family {
    font-family: var(--mono);
    font-size: .7rem;
    color: var(--ink-muted);
  }
</style>

<div class="plate-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/">← ERIC GITONGA</a>
    <span class="crumb-sep">/</span>
    <a href="/dudus">DUDUS</a>
    <span class="crumb-sep">/</span>
    <a href="/dudus/identify">ORDERS</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page"><?= htmlspecialchars((string)$orderName, ENT_QUOTES, 'UTF-8') ?></span>
  </div>

  <h1>Order: <?= htmlspecialchars((string)$orderName, ENT_QUOTES, 'UTF-8') ?></h1>
  <p style="font-family: var(--serif); font-size: 1.05rem; color: var(--ink-muted);">
    <?= count($cards) ?> species documented in Kenya.
  </p>

  <div class="species-grid">
    <?php foreach ($cards as $c): ?>
      <a class="species-card" href="/dudus/card/<?= urlencode((string)$c['id']) ?>">
        <?php if (!empty($c['image_url'])): ?>
          <img class="species-thumb" src="<?= htmlspecialchars((string)$c['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$c['common_name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
        <?php endif; ?>
        <div class="species-info">
          <h2 class="species-title"><?= htmlspecialchars((string)$c['common_name'], ENT_QUOTES, 'UTF-8') ?></h2>
          <span class="species-family"><?= htmlspecialchars((string)$c['family'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
