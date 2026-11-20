<?php
declare(strict_types=1);

namespace EricGitonga\Controllers;

use EricGitonga\Services\BlogService;

final class BlogController
{
    public function index(): void
    {
        $posts = BlogService::getAllPosts();
        $title = 'Blog — Eric Gitonga';
        $description = 'Tech and art — software, photography, drawing, watercolour, and dudus.';
        $currentSection = 'blog';

        ob_start();
        require dirname(__DIR__) . '/Views/blog/index.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function post(array $params): void
    {
        $slug = $params['slug'] ?? '';
        $post = BlogService::getPost($slug);
        if ($post === null) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/404.php';
            return;
        }

        $title = $post['title'] . ' — Eric Gitonga';
        $description = $post['excerpt'];
        $currentSection = 'blog';

        ob_start();
        require dirname(__DIR__) . '/Views/blog/post.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }
}
