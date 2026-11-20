<?php
declare(strict_types=1);

namespace EricGitonga\Services;

use EricGitonga\Config\AppConfig;

final class BlogService
{
    public static function getAllPosts(): array
    {
        $dir = AppConfig::contentDir('blog');
        if (!is_dir($dir)) {
            return [];
        }
        $files = glob($dir . '/*.md') ?: [];
        $posts = [];
        foreach ($files as $file) {
            $content = (string)file_get_contents($file);
            $parsed = MarkdownParser::parse($content);
            $meta = $parsed['meta'];
            $slug = $meta['slug'] ?? pathinfo($file, PATHINFO_FILENAME);
            $date = $meta['date'] ?? date('Y-m-d', filemtime($file));
            $posts[] = [
                'slug' => $slug,
                'title' => $meta['title'] ?? ucfirst(str_replace('-', ' ', $slug)),
                'date' => $date,
                'excerpt' => $meta['excerpt'] ?? '',
                'body_html' => $parsed['html'],
                'file' => basename($file),
            ];
        }

        usort($posts, fn($a, $b) => strcmp($b['date'], $a['date']));
        return $posts;
    }

    public static function getPost(string $slug): ?array
    {
        $posts = self::getAllPosts();
        foreach ($posts as $post) {
            if ($post['slug'] === $slug) {
                return $post;
            }
        }
        return null;
    }
}
