<?php
declare(strict_types=1);

namespace EricGitonga\Controllers;

use EricGitonga\Config\AppConfig;

final class HubController
{
    public function index(): void
    {
        $title = 'Eric Gitonga';
        $description = 'Eric Gitonga — software, photography, drawing and watercolour, and entomology, across four linked plates.';
        $currentSection = 'hub';

        ob_start();
        require dirname(__DIR__) . '/Views/hub/index.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function daguerreotypes(): void
    {
        $title = 'Daguerreotypes — Eric Gitonga';
        $description = 'Photographs — landscapes, portraits, and documentary moments.';
        $currentSection = 'daguerreotypes';

        ob_start();
        require dirname(__DIR__) . '/Views/gallery/daguerreotypes.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function daubs(): void
    {
        $title = 'Daubs — Eric Gitonga';
        $description = 'Drawings, watercolours, and marks made for their own sake.';
        $currentSection = 'daubs';

        ob_start();
        require dirname(__DIR__) . '/Views/hub/daubs.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function diffs(): void
    {
        $title = 'Diffs — Eric Gitonga';
        $description = 'Software and systems — Eric Gitonga\'s technical practice.';
        $currentSection = 'diffs';

        ob_start();
        require dirname(__DIR__) . '/Views/hub/diffs.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function health(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'ok',
            'version' => trim((string)@file_get_contents(AppConfig::rootDir() . '/VERSION')),
            'env' => AppConfig::isPreview() ? 'preview' : (AppConfig::isProduction() ? 'production' : 'development'),
            'timestamp' => time(),
        ]);
    }
}
