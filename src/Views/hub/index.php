<style>
  .wall {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
  }
  .plate {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    border-right: 1px solid var(--line);
    background: var(--ground);
    transition: background .2s ease;
  }
  .plate:last-child { border-right: none; }
  .art {
    position: relative;
    flex: 1;
    min-height: 260px;
    overflow: hidden;
    background: #000;
  }
  .art img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .6s ease;
  }
  .art--left img { object-position: left; }
  .plate:hover .art img, .plate:focus-visible .art img {
    transform: scale(1.05);
  }
  .label {
    padding: 1.2rem 1.4rem 1.4rem;
    border-top: 1px solid var(--line);
    background: var(--ground);
  }
  .plate-no {
    display: block;
    font-family: var(--mono);
    font-size: .65rem;
    letter-spacing: .15em;
    color: var(--ink-muted);
    margin-bottom: .25rem;
  }
  .label h2 {
    font-family: var(--serif);
    font-style: italic;
    font-weight: 500;
    font-size: 1.85rem;
    line-height: 1.1;
    margin: 0 0 .3rem;
  }
  .dest {
    display: block;
    font-family: var(--mono);
    font-size: .7rem;
    letter-spacing: .05em;
    color: var(--accent);
  }

  @media (max-width: 900px) {
    .wall { grid-template-columns: repeat(2, 1fr); }
    .plate:nth-child(2) { border-right: none; }
    .plate:nth-child(1), .plate:nth-child(2) { border-bottom: 1px solid var(--line); }
  }
  @media (max-width: 580px) {
    .wall { grid-template-columns: 1fr; }
    .plate { border-right: none; border-bottom: 1px solid var(--line); }
    .plate:last-child { border-bottom: none; }
  }
</style>

<main class="wall">
  <a class="plate" href="/dudus" data-cat="dudus">
    <div class="art">
      <img src="/assets/hub/dudus.jpg" alt="Macro photograph of a bee perched on a twig" width="1500" height="1000" loading="lazy">
    </div>
    <div class="label">
      <span class="plate-no">PLATE I</span>
      <h2>Dudus</h2>
      <span class="dest">ericgitonga.com/dudus</span>
    </div>
  </a>
    
  <a class="plate" href="/daguerreotypes" data-cat="daguerreotypes">
    <div class="art">
      <img src="/assets/hub/daguerreotypes.jpg" alt="A solitary baobab-like tree trunk against dawn sky" width="1499" height="1000" loading="lazy">
    </div>
    <div class="label">
      <span class="plate-no">PLATE II</span>
      <h2>Daguerreotypes</h2>
      <span class="dest">ericgitonga.com/daguerreotypes</span>
    </div>
  </a>

  <a class="plate" href="/daubs" data-cat="daubs">
    <div class="art">
      <img src="/assets/hub/daubs.jpg" alt="An ink illustration of an insect head in progress" width="1300" height="1000" loading="lazy">
    </div>
    <div class="label">
      <span class="plate-no">PLATE III</span>
      <h2>Daubs</h2>
      <span class="dest">ericgitonga.com/daubs</span>
    </div>
  </a>

  <a class="plate" href="/diffs" data-cat="diffs">
    <div class="art art--left">
      <img src="/assets/hub/diffs.jpg" alt="Software source file" width="1226" height="1000" loading="lazy">
    </div>
    <div class="label">
      <span class="plate-no">PLATE IV</span>
      <h2>Diffs</h2>
      <span class="dest">ericgitonga.com/diffs</span>
    </div>
  </a>
</main>
