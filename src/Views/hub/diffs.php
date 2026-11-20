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
  .entries {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: .75rem;
  }
  .entry {
    border: 1px solid var(--line);
    background: var(--ground-raised);
    transition: border-color .15s ease;
  }
  .entry:hover { border-color: var(--accent); }
  .entry a {
    display: block;
    padding: 1.2rem 1.4rem;
  }
  .entry-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
  }
  .entry-name {
    font-family: var(--mono);
    font-weight: 500;
    font-size: 1rem;
    color: var(--ink);
  }
  .entry-name .slash { color: var(--accent); }
  .entry-arrow {
    font-family: var(--mono);
    color: var(--ink-muted);
    transition: transform .15s ease, color .15s ease;
  }
  .entry:hover .entry-arrow {
    transform: translateX(3px);
    color: var(--accent);
  }
  .entry-desc {
    margin: .4rem 0 0;
    font-family: var(--serif);
    font-size: .95rem;
    color: var(--ink-muted);
    line-height: 1.45;
  }
</style>

<div class="plate-page">
  <div class="breadcrumb" aria-label="Breadcrumb">
    <a href="/">← ERIC GITONGA</a>
    <span class="crumb-sep">/</span>
    <span aria-current="page">DIFFS</span>
  </div>

  <span class="plate-no">PLATE IV</span>
  <h1>Diffs</h1>
  <p class="intro">Software and systems — Eric Gitonga's technical practice. Selected tools, platforms, and products.</p>

  <ul class="entries">
    <li class="entry">
      <a href="https://www.busherianbiasharas.com" target="_blank" rel="noopener">
        <div class="entry-top">
          <span class="entry-name">busherian<span class="slash">/</span>biasharas</span>
          <span class="entry-arrow">→</span>
        </div>
        <p class="entry-desc">Alliance High School & Alliance Girls High School alumni business directory, connecting verified busherian businesses worldwide.</p>
      </a>
    </li>

    <li class="entry">
      <a href="/dudus">
        <div class="entry-top">
          <span class="entry-name">dudus<span class="slash">/</span>companion</span>
          <span class="entry-arrow">→</span>
        </div>
        <p class="entry-desc">Kenyan entomology field companion and photographic identification system for over 200 arthropod species.</p>
      </a>
    </li>

    <li class="entry">
      <a href="/shop">
        <div class="entry-top">
          <span class="entry-name">print<span class="slash">/</span>shop</span>
          <span class="entry-arrow">→</span>
        </div>
        <p class="entry-desc">Fine art archival print shop with interactive room wall mockups and direct M-Pesa payment validation.</p>
      </a>
    </li>
  </ul>
</div>
