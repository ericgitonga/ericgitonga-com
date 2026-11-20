<style>
  .plate-page {
    max-width: 960px;
    margin: 0 auto;
    padding: clamp(2rem, 5vw, 3.5rem) 1.5rem 4rem;
    width: 100%;
  }
  .plate-no {
    display: block;
    font-family: var(--mono);
    font-size: .68rem;
    letter-spacing: .15em;
    color: var(--ink-muted);
    margin-bottom: .4rem;
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
  section.group { margin-top: clamp(2rem, 4vw, 2.5rem); }
  .group-label {
    display: flex;
    align-items: baseline;
    gap: .75rem;
    font-family: var(--mono);
    font-size: .72rem;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--ink-muted);
    margin: 0 0 .9rem;
  }
  .group-label::after {
    content: "";
    flex: 1;
    height: 1px;
    background: var(--line);
  }
</style>

<div class="plate-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/">← ERIC GITONGA</a>
    <span class="crumb-sep">/</span>
    <a href="/dudus">DUDUS</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page">DUTRAITS</span>
  </div>

  <span class="plate-no">PLATE I · GALLERY</span>
  <h1>Dutraits</h1>
  <p class="intro">Portraits of Kenyan arthropods — lit, staged, and documented in living macro detail.</p>

  <section class="group" aria-labelledby="dudus-gallery-label">
    <p class="group-label" id="dudus-gallery-label">Galleries</p>
    <div id="gallery-root"><p style="font-family: var(--mono); font-size: .85rem; color: var(--ink-muted);">Loading albums…</p></div>
  </section>
</div>

<script src="/assets/gallery.js" defer></script>
<script defer>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof initGallery === 'function') {
      initGallery('dudus');
    }
  });
</script>
