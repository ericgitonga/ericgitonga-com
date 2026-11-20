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
  .album-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1.25rem;
  }
  .album-card {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    text-align: left;
    color: inherit;
  }
  .album-thumb {
    width: 100%;
    aspect-ratio: 3/2;
    object-fit: cover;
    background: var(--ground-raised);
    border: 1px solid var(--line);
    transition: transform .2s ease;
  }
  .album-card:hover .album-thumb { transform: scale(1.03); }
  .album-title {
    font-family: var(--serif);
    font-size: 1.15rem;
    font-style: italic;
  }
  .album-count {
    font-family: var(--mono);
    font-size: .7rem;
    color: var(--ink-muted);
  }
</style>

<div class="plate-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/">← ERIC GITONGA</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page">DAGUERREOTYPES</span>
  </div>

  <span class="plate-no">PLATE II</span>
  <h1>Daguerreotypes</h1>
  <p class="intro">Photographs — landscapes, portraits, and documentary moments from Kenya and beyond.</p>

  <section class="group" aria-labelledby="photos-label">
    <p class="group-label" id="photos-label">Photographs</p>
    <div id="gallery-root"><p style="font-family: var(--mono); font-size: .85rem; color: var(--ink-muted);">Loading albums…</p></div>
  </section>
</div>

<script src="/assets/gallery.js" defer></script>
<script defer>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof initGallery === 'function') {
      initGallery('daguerreotypes');
    }
  });
</script>
