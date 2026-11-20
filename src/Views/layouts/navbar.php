<?php
declare(strict_types=1);

/** @var string $currentSection */
$currentSection = $currentSection ?? '';
?>
<header class="topbar">
  <nav class="topbar-plates" aria-label="Plates">
    <?php if ($currentSection === 'dudus'): ?>
      <a href="/dudus" aria-current="page">DUDUS</a>
    <?php else: ?>
      <a href="/dudus">DUDUS</a>
    <?php endif; ?>

    <?php if ($currentSection === 'daguerreotypes'): ?>
      <a href="/daguerreotypes" aria-current="page">DAGUERREOTYPES</a>
    <?php else: ?>
      <a href="/daguerreotypes">DAGUERREOTYPES</a>
    <?php endif; ?>

    <?php if ($currentSection === 'daubs'): ?>
      <a href="/daubs" aria-current="page">DAUBS</a>
    <?php else: ?>
      <a href="/daubs">DAUBS</a>
    <?php endif; ?>

    <?php if ($currentSection === 'diffs'): ?>
      <a href="/diffs" aria-current="page">DIFFS</a>
    <?php else: ?>
      <a href="/diffs">DIFFS</a>
    <?php endif; ?>
  </nav>

  <nav class="topbar-links" aria-label="Site Navigation">
    <a href="#bio-modal">Bio</a>
    <?php if ($currentSection === 'blog'): ?>
      <a href="/blog" aria-current="page">Blog</a>
    <?php else: ?>
      <a href="/blog">Blog</a>
    <?php endif; ?>

    <?php if ($currentSection === 'shop'): ?>
      <a href="/shop" aria-current="page">Shop</a>
    <?php else: ?>
      <a href="/shop">Shop</a>
    <?php endif; ?>
    <a href="#contact-modal">Contact</a>
  </nav>
</header>
