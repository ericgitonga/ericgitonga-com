<?php
declare(strict_types=1);

namespace EricGitonga\Services;

use EricGitonga\Config\AppConfig;

final class MediaService
{
    public static function getManifest(): array
    {
        $bundled = AppConfig::contentDir('media/manifest.json');
        if (!is_file($bundled)) {
            return [
                'generatedAt' => date('c'),
                'plates' => [
                    'dudus' => [],
                    'daguerreotypes' => [],
                    'daubs' => [],
                    'drops' => [],
                ],
            ];
        }

        $data = json_decode((string)file_get_contents($bundled), true);
        if (!is_array($data) || empty($data['plates'])) {
            return ['generatedAt' => date('c'), 'plates' => []];
        }

        // In preview or local dev, prefer local paths for bundled samples if present
        if (AppConfig::isPreview() || AppConfig::isLocal()) {
            foreach ($data['plates'] as $plateKey => &$albums) {
                foreach ($albums as &$album) {
                    $slug = $album['slug'] ?? $album['title'];
                    $localCover = "media/{$plateKey}/{$slug}/cover.jpg";
                    if (is_file(AppConfig::publicDir($localCover))) {
                        $album['cover'] = '/' . $localCover;
                    }
                    if (!empty($album['images']) && is_array($album['images'])) {
                        foreach ($album['images'] as &$img) {
                            $fname = $img['filename'] ?? '';
                            $localImg = "media/{$plateKey}/{$slug}/{$fname}";
                            if (is_file(AppConfig::publicDir($localImg))) {
                                $img['url'] = '/' . $localImg;
                            }
                        }
                    }
                }
            }
            unset($albums, $album, $img);
        }

        return $data;
    }
}
