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
  .card-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-top: 2rem;
  }
  .card-tile {
    display: flex;
    flex-direction: column;
    border: 1px solid var(--line);
    background: var(--ground-raised);
    transition: border-color .15s ease;
  }
  .card-tile:hover { border-color: var(--accent); }
  .card-art {
    display: block;
    width: 100%;
    aspect-ratio: 3/2;
    overflow: hidden;
    background: #000;
  }
  .card-art img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .3s ease;
  }
  .card-tile:hover .card-art img { transform: scale(1.04); }
  .card-label {
    padding: 1.2rem;
    display: flex;
    flex-direction: column;
    gap: .4rem;
  }
  .card-title {
    font-family: var(--serif);
    font-style: italic;
    font-size: 1.4rem;
    font-weight: 500;
    margin: 0;
  }
  .card-desc {
    font-family: var(--serif);
    font-size: .95rem;
    color: var(--ink-muted);
    margin: 0;
    line-height: 1.4;
  }
  .search-bar {
    margin-top: 3rem;
    padding: 1.5rem;
    border: 1px solid var(--line);
    background: var(--ground-raised);
    display: flex;
    gap: .8rem;
    align-items: center;
  }
  .search-bar input {
    flex: 1;
    padding: .6rem .8rem;
    border: 1px solid var(--line);
    background: var(--ground);
    font-family: var(--label);
    font-size: 1rem;
    color: var(--ink);
  }
  .search-bar button {
    padding: .6rem 1.4rem;
    background: var(--accent);
    color: var(--ground);
    border: none;
    font-family: var(--mono);
    font-size: .78rem;
    text-transform: uppercase;
    cursor: pointer;
  }
  .search-bar button:hover { background: var(--ink); }

  @media (max-width: 640px) {
    .card-grid { grid-template-columns: 1fr; }
    .search-bar { flex-direction: column; }
    .search-bar input, .search-bar button { width: 100%; }
  }
</style>

<div class="plate-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/">← ERIC GITONGA</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page">DUDUS</span>
  </div>

  <span class="plate-no">PLATE I</span>
  <h1>Dudus</h1>
  <p class="intro">Kenya's tiniest wildlife — observed in life, photographed in macro fidelity, and organized by taxonomic order.</p>

  <div class="card-grid">
    <a class="card-tile" href="/dudus/dutraits">
      <div class="card-art">
        <img src="/assets/dudus/dutraits.jpg" alt="Macro photograph of insect details" width="1500" height="1000" loading="lazy">
      </div>
      <div class="card-label">
        <h2 class="card-title">Dutraits</h2>
        <p class="card-desc">The album-grid photography gallery of Eric Gitonga's own macro dudu portraits.</p>
      </div>
    </a>

    <a class="card-tile" href="/dudus/identify">
      <div class="card-art">
        <img src="/assets/dudus/dudepth.jpg" alt="Identification reference guide" width="1500" height="1000" loading="lazy">
      </div>
      <div class="card-label">
        <h2 class="card-title">Identify a Dudu</h2>
        <p class="card-desc">Interactive taxonomic companion for learning and identifying Kenyan insects and arachnids.</p>
      </div>
    </a>
  </div>

  <form class="search-bar" action="/dudus/search" method="GET">
    <input type="text" name="q" placeholder="Search by common name, order, or habitat (e.g. Mantis, Coleoptera)..." required>
    <button type="submit">Search Dudus</button>
  </form>
</div>
