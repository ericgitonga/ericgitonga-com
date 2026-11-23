<?php
/** @var array $items */
/** @var int $total */
$proof = \EricGitonga\Security\AntiSpamGate::generateProofToken();
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
    <a href="/shop">SHOP</a>
    <span class="crumb-sep">/</span>
    <a href="/shop/cart">← CART</a>
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

  <form class="checkout-form" id="checkout-form" method="POST" action="/api/shop/checkout">
    <!-- 7-Layer Anti-Spam Security Tokens (#28, H03) -->
    <div class="form-field hp-field" style="display:none;" aria-hidden="true">
      <label for="order-website">Website</label>
      <input id="order-website" name="website" type="text" tabindex="-1" autocomplete="nope">
    </div>
    <input type="hidden" name="ts" value="<?= (int)$proof['timestamp'] ?>">
    <input type="hidden" name="salt" value="<?= htmlspecialchars((string)$proof['salt'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="sig" value="<?= htmlspecialchars((string)$proof['signature'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="_proof" id="order-proof" value="">

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

    <!-- Kenya DPA 2019 Section 29 Notice & Consent (#30, H05) -->
    <div style="font-size: 0.8rem; color: var(--ink-muted); line-height: 1.5; border-left: 2px solid var(--accent); padding-left: 0.8rem; margin: 0.5rem 0;">
      <strong>Privacy Notice (Kenya DPA 2019):</strong> We collect your name, email, phone number, and delivery details solely to process, deliver, and verify your order. Your details are never sold, shared with third parties, or used for unsolicited marketing.
    </div>
    <div class="form-field" style="flex-direction: row; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
      <input type="checkbox" id="order-dpa-consent" name="dpa_consent" value="yes" required style="margin-top: 0.2rem; width: auto;">
      <label for="order-dpa-consent" style="text-transform: none; letter-spacing: normal; font-size: 0.85rem; color: var(--ink); line-height: 1.4;">
        I consent to the collection and processing of my contact and delivery details solely to fulfill this order.
      </label>
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
    // Populate Layer 3 Proof Token on real user interaction (#28)
    const proofInput = document.getElementById('order-proof');
    const setProof = () => {
      if (proofInput && !proofInput.value) {
        proofInput.value = 'human_' + Date.now();
      }
    };
    ['focusin', 'keydown', 'pointerdown', 'touchstart'].forEach(evt => {
      form.addEventListener(evt, setProof, { passive: true, once: true });
    });

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
