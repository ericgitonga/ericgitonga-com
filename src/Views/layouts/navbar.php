<?php
declare(strict_types=1);

/** @var string $currentSection */
$currentSection = $currentSection ?? '';
$isHub = ($currentSection === 'hub');
?>
<header class="topbar">
  <?php if ($isHub): ?>
    <span class="topbar-home">ERIC GITONGA</span>
  <?php else: ?>
    <a href="/" class="topbar-home">ERIC GITONGA</a>
  <?php endif; ?>

  <nav class="topbar-plates" aria-label="Plates">
    <?php if ($currentSection === 'dudus'): ?>
      <span aria-current="page">Dudus</span>
    <?php else: ?>
      <a href="/dudus">Dudus</a>
    <?php endif; ?>

    <?php if ($currentSection === 'daguerreotypes'): ?>
      <span aria-current="page">Daguerreotypes</span>
    <?php else: ?>
      <a href="/daguerreotypes">Daguerreotypes</a>
    <?php endif; ?>

    <?php if ($currentSection === 'daubs'): ?>
      <span aria-current="page">Daubs</span>
    <?php else: ?>
      <a href="/daubs">Daubs</a>
    <?php endif; ?>

    <?php if ($currentSection === 'diffs'): ?>
      <span aria-current="page">Diffs</span>
    <?php else: ?>
      <a href="/diffs">Diffs</a>
    <?php endif; ?>
  </nav>

  <nav class="topbar-links" aria-label="Site Navigation">
    <a href="#bio-modal">Bio</a>
    <?php if ($currentSection === 'blog'): ?>
      <span aria-current="page">Blog</span>
    <?php else: ?>
      <a href="/blog">Blog</a>
    <?php endif; ?>

    <?php if ($currentSection === 'shop'): ?>
      <span aria-current="page">Shop</span>
    <?php else: ?>
      <a href="/shop">Shop</a>
    <?php endif; ?>
    <a href="#contact-modal">Contact</a>
  </nav>
</header>
