<?php
/** @var array $orders */
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
  .intro {
    font-family: var(--serif);
    font-size: 1.05rem;
    color: var(--ink-muted);
    max-width: 36em;
    margin: 0 0 2rem;
    line-height: 1.5;
  }
  .orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
  }
  .order-card {
    border: 1px solid var(--line);
    background: var(--ground-raised);
    padding: 1.2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: border-color .15s ease;
  }
  .order-card:hover { border-color: var(--accent); }
  .order-name {
    font-family: var(--serif);
    font-size: 1.2rem;
    font-style: italic;
  }
  .order-count {
    font-family: var(--mono);
    font-size: .75rem;
    color: var(--accent);
  }
</style>

<div class="plate-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/dudus">← DUDUS</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page">IDENTIFY</span>
  </div>

  <h1>Identify a Dudu</h1>
  <p class="intro">Browse species by major taxonomic order, or search the Kenyan entomology catalog.</p>

  <div class="orders-grid">
    <?php foreach ($orders as $orderName => $count): ?>
      <a class="order-card" href="/dudus/orders/<?= urlencode((string)$orderName) ?>">
        <span class="order-name"><?= htmlspecialchars((string)$orderName, ENT_QUOTES, 'UTF-8') ?></span>
        <span class="order-count"><?= (int)$count ?> species →</span>
      </a>
    <?php endforeach; ?>
  </div>
</div>
