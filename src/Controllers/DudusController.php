<?php
declare(strict_types=1);

namespace EricGitonga\Controllers;

use EricGitonga\Services\DudusCatalogService;

final class DudusController
{
    public function index(): void
    {
        $title = 'Dudus — Kenya\'s Tiniest Wildlife — Eric Gitonga';
        $description = 'Kenya\'s tiniest wildlife — macro photography gallery and identification companion.';
        $currentSection = 'dudus';

        ob_start();
        require dirname(__DIR__) . '/Views/dudus/index.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function dutraits(): void
    {
        $title = 'Dutraits — Dudu Portraits — Eric Gitonga';
        $description = 'Macro photography albums of Kenya\'s tiniest wildlife.';
        $currentSection = 'dudus';

        ob_start();
        require dirname(__DIR__) . '/Views/gallery/dutraits.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function identify(): void
    {
        $title = 'Identify a Dudu — Eric Gitonga';
        $description = 'Interactive step-by-step identification wizard for Kenyan arthropods.';
        $currentSection = 'dudus';
        $orders = DudusCatalogService::getOrders();

        ob_start();
        require dirname(__DIR__) . '/Views/dudus/identify.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function card(array $params): void
    {
        $id = $params['id'] ?? '';
        $card = DudusCatalogService::getById($id);
        if ($card === null) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/404.php';
            return;
        }

        $title = $card['common_name'] . ' — Dudus — Eric Gitonga';
        $description = $card['blurb'] ?: "Kenyan dudu profile for {$card['common_name']} ({$card['order']}).";
        $currentSection = 'dudus';

        ob_start();
        require dirname(__DIR__) . '/Views/dudus/card.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function orders(array $params): void
    {
        $order = $params['order'] ?? '';
        $cards = DudusCatalogService::getByOrder($order);
        if (empty($cards)) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/404.php';
            return;
        }

        $title = "Order: {$order} — Dudus — Eric Gitonga";
        $description = "Kenyan arthropod species in Order {$order}.";
        $currentSection = 'dudus';

        ob_start();
        require dirname(__DIR__) . '/Views/dudus/orders.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function search(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $results = DudusCatalogService::search($q);
        $title = 'Search Dudus — Eric Gitonga';
        $description = 'Search Kenyan arthropod species by common name, order, or habitat.';
        $currentSection = 'dudus';

        ob_start();
        require dirname(__DIR__) . '/Views/dudus/search.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function apiSearch(): void
    {
        header('Content-Type: application/json');
        $q = trim((string)($_GET['q'] ?? ''));
        echo json_encode(DudusCatalogService::search($q));
    }
}
