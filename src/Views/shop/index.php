<style>
  .shop-page {
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
    margin: 0 0 2.5rem;
    line-height: 1.5;
  }
  .collections-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
  }
  .coll-card {
    border: 1px solid var(--line);
    background: var(--ground-raised);
    display: flex;
    flex-direction: column;
    transition: border-color .15s ease;
  }
  .coll-card:hover { border-color: var(--accent); }
  .coll-art {
    width: 100%;
    aspect-ratio: 16/9;
    object-fit: cover;
    background: #000;
  }
  .coll-info {
    padding: 1.4rem;
    display: flex;
    flex-direction: column;
    gap: .4rem;
  }
  .coll-title {
    font-family: var(--serif);
    font-style: italic;
    font-size: 1.5rem;
    font-weight: 500;
    margin: 0;
  }
  .coll-desc {
    font-family: var(--serif);
    font-size: 1rem;
    color: var(--ink-muted);
    margin: 0;
    line-height: 1.45;
  }
  .coll-link {
    display: inline-block;
    margin-top: .8rem;
    font-family: var(--mono);
    font-size: .75rem;
    text-transform: uppercase;
    color: var(--accent);
  }
  @media (max-width: 650px) {
    .collections-grid { grid-template-columns: 1fr; }
  }
</style>

<div class="shop-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/">← ERIC GITONGA</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page">SHOP</span>
  </div>

  <h1>Archival Print Shop</h1>
  <p class="intro">Fine art archival prints, apparel, and coasters. Printed to order, framed on museum-grade cotton rag, and payable via M-Pesa.</p>

  <div class="collections-grid">
    <a class="coll-card" href="/shop/prints">
      <img class="coll-art" src="/assets/dudus/dumerch.jpg" alt="Dudu prints preview" loading="lazy">
      <div class="coll-info">
        <h2 class="coll-title">Dudu Prints</h2>
        <p class="coll-desc">Kenyan insect macro photography on archival cotton paper, sized from Bookmark to A0 with live living-room wall scaling.</p>
        <span class="coll-link">Explore Collection →</span>
      </div>
    </a>

    <a class="coll-card" href="/shop/daguerreotypes">
      <img class="coll-art" src="/assets/hub/daguerreotypes.jpg" alt="Daguerreotypes prints preview" loading="lazy">
      <div class="coll-info">
        <h2 class="coll-title">Daguerreotypes Prints</h2>
        <p class="coll-desc">Fine art landscapes, portraits, and documentary photography. Standard sizes priced 30% below base macro prints.</p>
        <span class="coll-link">Explore Collection →</span>
      </div>
    </a>

    <a class="coll-card" href="/shop/apparel">
      <div class="coll-info">
        <h2 class="coll-title">Apparel</h2>
        <p class="coll-desc">Screen-printed heavy cotton t-shirts featuring selected Kenyan arthropod illustrations.</p>
        <span class="coll-link">View Apparel →</span>
      </div>
    </a>

    <a class="coll-card" href="/shop/coasters">
      <div class="coll-info">
        <h2 class="coll-title">Coasters</h2>
        <p class="coll-desc">Glossy ceramic, cork-backed coaster sets of four macro photograph plates.</p>
        <span class="coll-link">View Coasters →</span>
      </div>
    </a>
  </div>
</div>
