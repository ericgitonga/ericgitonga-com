<?php
declare(strict_types=1);

namespace EricGitonga\Controllers;

use EricGitonga\Services\MediaService;

final class GalleryController
{
    public function manifest(): void
    {
        header('Content-Type: application/json');
        echo json_encode(MediaService::getManifest());
    }
}
