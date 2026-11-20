<?php
declare(strict_types=1);

/**
 * @var string $title
 * @var string $description
 * @var string $content
 * @var string $currentSection
 * @var string $extraCss
 * @var string $extraJs
 */
$title = $title ?? 'Eric Gitonga';
$description = $description ?? 'Software, photography, drawing and watercolour, and entomology.';
$currentSection = $currentSection ?? '';
$extraCss = $extraCss ?? '';
$extraJs = $extraJs ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">

  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">
  <link rel="icon" href="/favicon-16x16.png" type="image/png" sizes="16x16">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo+Narrow:ital,wght@0,500;0,600;1,500&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400;1,6..72,500&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/brand.css">
  <?= $extraCss ?>
</head>
<body>
<div class="page-wrapper">
  <?php require __DIR__ . '/navbar.php'; ?>

  <?= $content ?>

  <?php require __DIR__ . '/footer.php'; ?>
</div>

<?php require __DIR__ . '/modal-bio.php'; ?>
<?php require __DIR__ . '/modal-contact.php'; ?>

<script src="/assets/js/anti-spam.js"></script>
<?= $extraJs ?>
</body>
</html>
