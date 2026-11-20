<?php
/** @var array $photos */
/** @var array $sizes */
/** @var string $collection */
$titleHeader = ($collection === 'dudu') ? 'Dudu Prints' : 'Daguerreotypes Prints';
?>
<style>
  .shop-view {
    display: flex;
    flex-direction: column;
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1.5rem 4rem;
    width: 100%;
  }
  .mockup-container {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 2rem;
    align-items: start;
    margin-bottom: 3rem;
  }
  .stage-frame {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    background: #241f16;
    border: 1px solid var(--line);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .stage-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: .85;
  }
  .stage-art {
    position: relative;
    z-index: 2;
    max-width: 45%;
    max-height: 70%;
    box-shadow: 0 12px 36px rgba(0,0,0,.6);
    border: 8px solid #f3efe3;
    transition: transform .3s ease;
  }
  .order-panel {
    border: 1px solid var(--line);
    background: var(--ground-raised);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
  }
  .order-title {
    font-family: var(--serif);
    font-size: 1.5rem;
    font-style: italic;
    margin: 0;
  }
  .size-picker {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .5rem;
  }
  .size-btn {
    border: 1px solid var(--line);
    background: var(--ground);
    padding: .6rem .4rem;
    text-align: center;
    cursor: pointer;
    font-family: var(--mono);
    font-size: .75rem;
  }
  .size-btn.active {
    border-color: var(--accent);
    background: var(--accent);
    color: var(--ground);
  }
  .price-tag {
    font-family: var(--serif);
    font-size: 1.8rem;
    font-weight: 500;
    color: var(--ink);
    margin: 0;
  }
  .btn-add-cart {
    padding: .8rem 1.4rem;
    background: var(--accent);
    color: var(--ground);
    border: none;
    font-family: var(--mono);
    font-size: .82rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background .15s ease;
  }
  .btn-add-cart:hover { background: var(--ink); }
  .catalogue-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
  }
  .photo-tile {
    border: 1px solid var(--line);
    background: var(--ground-raised);
    cursor: pointer;
    overflow: hidden;
    transition: border-color .15s ease;
  }
  .photo-tile:hover, .photo-tile.selected { border-color: var(--accent); }
  .photo-tile img {
    width: 100%;
    aspect-ratio: 4/3;
    object-fit: cover;
    display: block;
  }
  .photo-tile span {
    display: block;
    padding: .5rem;
    font-family: var(--mono);
    font-size: .7rem;
    color: var(--ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  @media (max-width: 850px) {
    .mockup-container { grid-template-columns: 1fr; }
  }
</style>

<div class="shop-view">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/">← ERIC GITONGA</a>
    <span class="crumb-sep">/</span>
    <a href="/shop">SHOP</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page"><?= strtoupper($titleHeader) ?></span>
  </div>

  <div class="mockup-container">
    <div class="stage-frame" id="stage-frame">
      <img class="stage-bg" src="/assets/img/mockup/room.jpg" alt="Living room setting">
      <img class="stage-art" id="preview-art" src="<?= htmlspecialchars($photos[0]['src'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="Selected print">
    </div>

    <div class="order-panel">
      <h2 class="order-title" id="selected-title"><?= htmlspecialchars($photos[0]['title'] ?? 'Selected Print', ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="price-tag" id="selected-price">KES <?= number_format($sizes['A4']['price'] ?? 15000) ?></div>

      <div class="size-picker">
        <?php foreach ($sizes as $s => $spec): ?>
          <button type="button" class="size-btn <?= ($s === 'A4') ? 'active' : '' ?>" data-size="<?= $s ?>" data-price="<?= $spec['price'] ?>">
            <?= $s ?>
          </button>
        <?php endforeach; ?>
      </div>

      <button type="button" class="btn-add-cart" id="btn-add">Add to Cart</button>
      <div id="add-feedback" style="font-family: var(--mono); font-size: .75rem; color: var(--accent);"></div>
      <a href="/shop/cart" style="font-family: var(--mono); font-size: .75rem; color: var(--ink-muted); text-align: center;">View Cart →</a>
    </div>
  </div>

  <h3 style="font-family: var(--serif); font-style: italic; font-size: 1.5rem; margin: 0 0 1rem;">Select Photo</h3>
  <div class="catalogue-grid">
    <?php foreach ($photos as $idx => $p): ?>
      <div class="photo-tile <?= ($idx === 0) ? 'selected' : '' ?>" data-id="<?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') ?>" data-src="<?= htmlspecialchars($p['src'], ENT_QUOTES, 'UTF-8') ?>" data-title="<?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?>">
        <img src="<?= htmlspecialchars($p['thumb_sm'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
        <span><?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  let selectedPhotoId = "<?= htmlspecialchars($photos[0]['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>";
  let selectedSize = 'A4';

  const previewArt = document.getElementById('preview-art');
  const selectedTitle = document.getElementById('selected-title');
  const selectedPrice = document.getElementById('selected-price');
  const sizeBtns = document.querySelectorAll('.size-btn');
  const photoTiles = document.querySelectorAll('.photo-tile');
  const btnAdd = document.getElementById('btn-add');
  const feedback = document.getElementById('add-feedback');

  photoTiles.forEach(tile => {
    tile.addEventListener('click', () => {
      photoTiles.forEach(t => t.classList.remove('selected'));
      tile.classList.add('selected');
      selectedPhotoId = tile.dataset.id;
      if (previewArt) previewArt.src = tile.dataset.src;
      if (selectedTitle) selectedTitle.textContent = tile.dataset.title;
    });
  });

  sizeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      sizeBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      selectedSize = btn.dataset.size;
      const price = Number(btn.dataset.price).toLocaleString();
      if (selectedPrice) selectedPrice.textContent = 'KES ' + price;
    });
  });

  if (btnAdd) {
    btnAdd.addEventListener('click', async () => {
      if (!selectedPhotoId) return;
      btnAdd.disabled = true;
      feedback.textContent = 'Adding...';
      const fd = new FormData();
      fd.append('photo_id', selectedPhotoId);
      fd.append('size', selectedSize);
      fd.append('qty', '1');
      fd.append('product_type', 'print');

      try {
        const resp = await fetch('/api/shop/cart/add', { method: 'POST', body: fd });
        const res = await resp.json();
        if (res.success) {
          feedback.textContent = 'Added! (' + res.item_count + ' items in cart)';
        } else {
          feedback.textContent = 'Could not add item.';
        }
      } catch (err) {
        feedback.textContent = 'Error adding to cart.';
      } finally {
        btnAdd.disabled = false;
      }
    });
  }
});
</script>
