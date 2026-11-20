<?php
declare(strict_types=1);

namespace EricGitonga\Services;

use EricGitonga\Config\AppConfig;

final class MediaService
{
    public static function getManifest(): array
    {
        $cacheFile = AppConfig::storageDir('cache/manifest.json');
        if (is_file($cacheFile) && (time() - filemtime($cacheFile) < 86400)) {
            $cached = json_decode((string)file_get_contents($cacheFile), true);
            if (is_array($cached)) {
                return $cached;
            }
        }

        // Default / fallback manifest structure
        $manifest = [
            'daguerreotypes' => [
                'Beauty In The Ordinary' => [
                    'slug' => 'beauty-in-the-ordinary',
                    'title' => 'Beauty In The Ordinary',
                    'cover' => AppConfig::mediaBaseUrl() . '/Daguerreotypes/beauty-in-the-ordinary/cover.jpg',
                    'photos' => [],
                ],
                'Racecourse' => [
                    'slug' => 'racecourse',
                    'title' => 'Racecourse',
                    'cover' => AppConfig::mediaBaseUrl() . '/Daguerreotypes/racecourse/cover.jpg',
                    'photos' => [],
                ],
            ],
            'dudus' => [
                'Dutraits' => [
                    'slug' => 'dutraits',
                    'title' => 'Dutraits',
                    'cover' => AppConfig::mediaBaseUrl() . '/Dudus/dutraits/cover.jpg',
                    'photos' => [],
                ],
            ],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        @mkdir(dirname($cacheFile), 0775, true);
        file_put_contents($cacheFile, json_encode($manifest, JSON_PRETTY_PRINT));
        return $manifest;
    }
}
