<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Bootstrap.php';

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
