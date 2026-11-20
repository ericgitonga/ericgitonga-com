<?php
/** @var array $items */
/** @var int $total */
?>
<style>
  .checkout-page {
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
    margin: 0 0 1.5rem;
  }
  .mpesa-instructions {
    border: 1px solid var(--accent-dim);
    background: var(--ground-raised);
    padding: 1.4rem;
    margin-bottom: 2rem;
  }
  .mpesa-instructions h3 {
    font-family: var(--serif);
    font-style: italic;
    font-size: 1.3rem;
    margin: 0 0 .5rem;
    color: var(--accent);
  }
  .mpesa-instructions p {
    font-family: var(--serif);
    font-size: 1rem;
    margin: 0 0 .8rem;
    line-height: 1.5;
  }
  .mpesa-instructions ol {
    margin: 0 0 .8rem;
    padding-left: 1.2rem;
    font-family: var(--serif);
    font-size: 1rem;
    line-height: 1.6;
  }
  .checkout-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  .checkout-form .form-field {
    display: flex;
    flex-direction: column;
    gap: .25rem;
  }
  .checkout-form label {
    font-family: var(--mono);
    font-size: .72rem;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ink-muted);
  }
  .checkout-form input, .checkout-form textarea {
    padding: .6rem .75rem;
    border: 1px solid var(--line);
    background: var(--ground-raised);
    font-family: var(--label);
    font-size: 1rem;
  }
  .btn-submit-order {
    padding: .85rem 1.8rem;
    background: var(--accent);
    color: var(--ground);
    font-family: var(--mono);
    font-size: .82rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    border: none;
    cursor: pointer;
    align-self: flex-start;
    margin-top: .5rem;
  }
  .btn-submit-order:hover { background: var(--ink); }
</style>

<div class="checkout-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/">← ERIC GITONGA</a>
    <span class="crumb-sep">/</span>
    <a href="/shop">SHOP</a>
    <span class="crumb-sep">/</span>
    <a href="/shop/cart">CART</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page">CHECKOUT</span>
  </div>

  <h1>Checkout & Payment</h1>

  <div class="mpesa-instructions">
    <h3>M-Pesa Payment Instructions</h3>
    <p>Total Payable: <strong>KES <?= number_format($total) ?></strong></p>
    <ol>
      <li>Go to M-Pesa on your phone and select <strong>Send Money</strong> (or Buy Goods).</li>
      <li>Enter Number: <strong>0722 000 000</strong> (Eric Gitonga Mbaya).</li>
      <li>Enter Amount: <strong><?= $total ?></strong></li>
      <li>Enter your PIN and complete transaction.</li>
      <li>Copy the 10-character confirmation code (e.g. <code>QA12BC345D</code>) and paste it below.</li>
    </ol>
  </div>

  <form class="checkout-form" id="checkout-form">
    <div class="form-field">
      <label for="order-name">Your Full Name</label>
      <input id="order-name" name="name" type="text" required>
    </div>

    <div class="form-field">
      <label for="order-email">Email Address (for order tracking)</label>
      <input id="order-email" name="email" type="email" required>
    </div>

    <div class="form-field">
      <label for="order-phone">Phone Number</label>
      <input id="order-phone" name="phone" type="tel" required>
    </div>

    <div class="form-field">
      <label for="order-mpesa">M-Pesa Confirmation Code</label>
      <input id="order-mpesa" name="mpesa_code" type="text" placeholder="e.g. QA12BC345D" required pattern="[A-Za-z0-9]{8,15}">
    </div>

    <div class="form-field">
      <label for="order-address">Delivery Address / Courier Note</label>
      <textarea id="order-address" name="delivery_address" rows="3" placeholder="Town, Estate/Street, or preferred collection station" required></textarea>
    </div>

    <button type="submit" class="btn-submit-order" id="btn-submit-order">Confirm Payment & Submit Order</button>
    <p id="checkout-feedback" style="font-family: var(--mono); font-size: .8rem; margin-top: .5rem;"></p>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('checkout-form');
  const btn = document.getElementById('btn-submit-order');
  const feedback = document.getElementById('checkout-feedback');

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      btn.disabled = true;
      feedback.textContent = 'Verifying payment and registering order...';
      feedback.style.color = 'var(--ink)';

      const fd = new FormData(form);
      try {
        const resp = await fetch('/api/shop/checkout', { method: 'POST', body: fd });
        const res = await resp.json();
        if (resp.ok && res.success) {
          feedback.textContent = res.message;
          feedback.style.color = 'var(--accent)';
          form.reset();
          setTimeout(() => { window.location.href = '/shop'; }, 4000);
        } else {
          feedback.textContent = res.error || 'Failed to register order.';
          feedback.style.color = '#a8432f';
        }
      } catch (err) {
        feedback.textContent = 'Network error while registering order.';
        feedback.style.color = '#a8432f';
      } finally {
        btn.disabled = false;
      }
    });
  }
});
</script>
