<?php
/** @var array $items */
/** @var int $total */
?>
<style>
  .cart-page {
    max-width: 820px;
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
    margin: 0 0 1.5rem;
  }
  .cart-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
  }
  .cart-table th, .cart-table td {
    padding: 1rem .8rem;
    border-bottom: 1px solid var(--line);
    text-align: left;
  }
  .cart-table th {
    font-family: var(--mono);
    font-size: .7rem;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ink-muted);
  }
  .cart-item-title {
    font-family: var(--serif);
    font-style: italic;
    font-size: 1.15rem;
    font-weight: 500;
  }
  .cart-item-meta {
    font-family: var(--mono);
    font-size: .75rem;
    color: var(--ink-muted);
  }
  .cart-qty-input {
    width: 50px;
    padding: .3rem;
    border: 1px solid var(--line);
    background: var(--ground-raised);
    font-family: var(--mono);
    text-align: center;
  }
  .btn-remove {
    background: none;
    border: none;
    color: #a8432f;
    font-family: var(--mono);
    font-size: .72rem;
    cursor: pointer;
    text-decoration: underline;
  }
  .cart-total-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border: 1px solid var(--line);
    background: var(--ground-raised);
    margin-bottom: 2rem;
  }
  .total-label {
    font-family: var(--mono);
    font-size: .8rem;
    text-transform: uppercase;
    color: var(--ink-muted);
  }
  .total-amount {
    font-family: var(--serif);
    font-size: 2rem;
    font-weight: 500;
  }
  .cart-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .btn-checkout {
    padding: .85rem 1.8rem;
    background: var(--accent);
    color: var(--ground);
    font-family: var(--mono);
    font-size: .82rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    border: none;
    cursor: pointer;
  }
  .btn-checkout:hover { background: var(--ink); }
</style>

<div class="cart-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/">← ERIC GITONGA</a>
    <span class="crumb-sep">/</span>
    <a href="/shop">SHOP</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page">CART</span>
  </div>

  <h1>Your Cart</h1>

  <?php if (empty($items)): ?>
    <div style="border: 1px dashed var(--line); background: var(--ground-raised); padding: 3rem 2rem; text-align: center;">
      <p style="font-family: var(--serif); font-size: 1.1rem; color: var(--ink-muted); margin: 0 0 1rem;">Your shopping cart is currently empty.</p>
      <a href="/shop/prints" style="font-family: var(--mono); font-size: .8rem; color: var(--accent); text-decoration: underline;">Explore Prints →</a>
    </div>
  <?php else: ?>
    <table class="cart-table">
      <thead>
        <tr>
          <th>Item</th>
          <th>Size</th>
          <th>Price</th>
          <th>Qty</th>
          <th>Subtotal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $idx => $it): ?>
          <tr>
            <td>
              <div class="cart-item-title"><?= htmlspecialchars((string)$it['title'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="cart-item-meta"><?= htmlspecialchars((string)$it['collection'], ENT_QUOTES, 'UTF-8') ?></div>
            </td>
            <td style="font-family: var(--mono); font-size: .85rem;"><?= htmlspecialchars((string)$it['size'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="font-family: var(--serif);">KES <?= number_format($it['unit_price']) ?></td>
            <td>
              <input type="number" class="cart-qty-input" value="<?= (int)$it['qty'] ?>" min="1" max="20" data-index="<?= $idx ?>">
            </td>
            <td style="font-family: var(--serif); font-weight: 500;">KES <?= number_format($it['total_price']) ?></td>
            <td>
              <button type="button" class="btn-remove" data-index="<?= $idx ?>">Remove</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="cart-total-box">
      <span class="total-label">Total Amount</span>
      <span class="total-amount">KES <?= number_format($total) ?></span>
    </div>

    <div class="cart-actions">
      <a href="/shop/prints" style="font-family: var(--mono); font-size: .8rem; color: var(--ink-muted);">← Continue Shopping</a>
      <a href="/shop/checkout" class="btn-checkout">Proceed to Checkout →</a>
    </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.cart-qty-input').forEach(input => {
    input.addEventListener('change', async () => {
      const fd = new FormData();
      fd.append('index', input.dataset.index);
      fd.append('qty', input.value);
      await fetch('/api/shop/cart/update', { method: 'POST', body: fd });
      window.location.reload();
    });
  });

  document.querySelectorAll('.btn-remove').forEach(btn => {
    btn.addEventListener('click', async () => {
      const fd = new FormData();
      fd.append('index', btn.dataset.index);
      await fetch('/api/shop/cart/remove', { method: 'POST', body: fd });
      window.location.reload();
    });
  });
});
</script>
