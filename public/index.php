<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Bootstrap.php';

// Check for active deployment maintenance gate (#31, H07)
$maintFile = dirname(__DIR__) . '/.maintenance_deploy.json';
if (file_exists($maintFile)) {
    $maint = @json_decode((string)@file_get_contents($maintFile), true);
    if (is_array($maint) && !empty($maint['active'])) {
        $started = (int)($maint['started_at_ts'] ?? 0);
        if ($started > 0 && (time() - $started) < 300) {
            http_response_code(503);
            header('Retry-After: 5');
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html lang="en"><head><title>System Maintenance — ericgitonga.com</title>'
               . '<meta name="viewport" content="width=device-width, initial-scale=1">'
               . '<style>body{font-family:sans-serif;background:#0d0d0d;color:#e6e6e6;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;padding:1rem;text-align:center;}h1{font-weight:400;font-size:1.5rem;letter-spacing:0.05em;}p{color:#888;}</style>'
               . '</head><body><div><h1>UPDATING PLATFORM</h1><p>A scheduled release is currently being deployed. Please refresh in a few seconds.</p></div></body></html>';
            exit;
        }
    }
}

use EricGitonga\Router;
use EricGitonga\Controllers\HubController;
use EricGitonga\Controllers\GalleryController;
use EricGitonga\Controllers\DudusController;
use EricGitonga\Controllers\ShopController;
use EricGitonga\Controllers\BlogController;
use EricGitonga\Controllers\ContactController;

$router = new Router();

// ── Hub & Standard Pages ──
$router->get('/', [HubController::class, 'index']);
$router->get('/daguerreotypes', [HubController::class, 'daguerreotypes']);
$router->get('/daubs', [HubController::class, 'daubs']);
$router->get('/diffs', [HubController::class, 'diffs']);
$router->get('/drops', [HubController::class, 'drops']);
$router->get('/api/health', [HubController::class, 'health']);

// ── Media Manifest ──
$router->get('/api/media-manifest', [GalleryController::class, 'manifest']);

// ── Contact Submission (7-Layer Anti-Spam Gate) ──
$router->post('/api/contact', [ContactController::class, 'submit']);

// ── Blog ──
$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/{slug}', [BlogController::class, 'post']);

// ── Dudus Companion ──
$router->get('/dudus', [DudusController::class, 'index']);
$router->get('/dudus/dutraits', [DudusController::class, 'dutraits']);
$router->get('/dudus/identify', [DudusController::class, 'identify']);
$router->get('/dudus/card/{id}', [DudusController::class, 'card']);
$router->get('/dudus/orders/{order}', [DudusController::class, 'orders']);
$router->get('/dudus/search', [DudusController::class, 'search']);
$router->get('/dudus/{id}', [DudusController::class, 'card']);
$router->get('/cart', function() { header('Location: /shop/cart', true, 301); exit; });
$router->get('/checkout', function() { header('Location: /shop/checkout', true, 301); exit; });
$router->get('/api/dudus/search', [DudusController::class, 'apiSearch']);

// ── Shop & Cart ──
$router->get('/shop', [ShopController::class, 'index']);
$router->get('/shop/prints', [ShopController::class, 'prints']);
$router->get('/shop/daguerreotypes', [ShopController::class, 'daguerreotypes']);
$router->get('/shop/apparel', [ShopController::class, 'apparel']);
$router->get('/shop/coasters', [ShopController::class, 'coasters']);
$router->get('/shop/cart', [ShopController::class, 'cart']);
$router->post('/api/shop/cart/add', [ShopController::class, 'cartAdd']);
$router->post('/api/shop/cart/update', [ShopController::class, 'cartUpdate']);
$router->post('/api/shop/cart/remove', [ShopController::class, 'cartRemove']);
$router->get('/shop/checkout', [ShopController::class, 'checkout']);
$router->post('/api/shop/checkout', [ShopController::class, 'checkoutSubmit']);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
