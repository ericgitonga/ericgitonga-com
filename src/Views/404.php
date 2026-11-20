<?php
$title = 'Page Not Found — Eric Gitonga';
$description = 'The requested page could not be found.';
$currentSection = '';

ob_start();
?>
<main style="max-width: 680px; margin: 4rem auto; padding: 2rem 1.5rem; text-align: center;">
  <p style="font-family: var(--mono); font-size: .8rem; letter-spacing: .15em; color: var(--ink-muted);">404 / NOT FOUND</p>
  <h1 style="font-family: var(--serif); font-style: italic; font-weight: 500; font-size: 2.5rem; margin: .5rem 0 1rem;">This trail has ended.</h1>
  <p style="font-family: var(--serif); font-size: 1.1rem; color: var(--ink-muted); line-height: 1.5; margin-bottom: 2rem;">
    The page you are looking for does not exist, or has moved to a unified path.
  </p>
  <a href="/" style="font-family: var(--mono); font-size: .8rem; text-transform: uppercase; color: var(--accent); border-bottom: 1px solid var(--accent); padding-bottom: .2rem;">← Back to Home</a>
</main>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/base.php';
