<?php
declare(strict_types=1);

// Set testing environment
putenv('APP_ENV=testing');
putenv('APP_URL=https://preview.ericgitonga.com');
putenv('APP_HMAC_SECRET=test_secret_hmac_key_ericgitonga_2026_test_suite');
putenv('SIMULATE_EMAILS=yes');

$sessionDir = sys_get_temp_dir() . '/ericgitonga_sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0775, true);
}
session_save_path($sessionDir);

require_once dirname(__DIR__) . '/src/Bootstrap.php';

use EricGitonga\Config\AppConfig;
use EricGitonga\Router;
use EricGitonga\Security\AntiSpamGate;
use EricGitonga\Services\DudusCatalogService;
use EricGitonga\Services\ShopService;
use EricGitonga\Services\CartService;
use EricGitonga\Services\MpesaLedger;
use EricGitonga\Services\BlogService;
use EricGitonga\Services\MarkdownParser;

CartService::startSession();

$passed = 0;
$failed = 0;

function it(string $description, callable $fn): void {
    global $passed, $failed;
    try {
        $fn();
        echo "  ✓ {$description}\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "  ✗ {$description}: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        $failed++;
    }
}

function assertTrue(bool $condition, string $msg = 'Expected condition to be true'): void {
    if (!$condition) throw new \RuntimeException($msg);
}

function assertEquals(mixed $expected, mixed $actual, string $msg = ''): void {
    if ($expected !== $actual) {
        throw new \RuntimeException($msg ?: sprintf("Expected %s, got %s", var_export($expected, true), var_export($actual, true)));
    }
}

echo "Running Unit Tests for ericgitonga-com (PHP 8.3 / Preview Architecture)...\n\n";

// ── 1. AppConfig & Preview Detection ────────────────────────────────────────
it('AppConfig detects preview and testing environment cleanly', function () {
    assertTrue(AppConfig::isTesting(), 'isTesting should be true');
    assertTrue(!AppConfig::isProduction(), 'isProduction should be false in testing');
    assertEquals('https://preview.ericgitonga.com', AppConfig::appUrl(), 'appUrl should match configured preview URL');
});

it('AppConfig::isPreview returns true when host starts with preview.', function () {
    $_SERVER['HTTP_HOST'] = 'preview.ericgitonga.com';
    assertTrue(AppConfig::isPreview(), 'Should detect preview host');
    unset($_SERVER['HTTP_HOST']);
});

// ── 2. MarkdownParser & BlogService ─────────────────────────────────────────
it('MarkdownParser extracts YAML frontmatter and renders markdown', function () {
    $src = "---\ntitle: Test Post\nslug: test-post\ndate: 2026-11-20\nexcerpt: A test excerpt\n---\n# Header 1\n\nThis is **bold** text and [link](https://ericgitonga.com).";
    $parsed = MarkdownParser::parse($src);
    assertEquals('Test Post', $parsed['meta']['title']);
    assertEquals('test-post', $parsed['meta']['slug']);
    assertTrue(str_contains($parsed['html'], '<h1>Header 1</h1>'), 'Renders h1');
    assertTrue(str_contains($parsed['html'], '<strong>bold</strong>'), 'Renders strong');
    assertTrue(str_contains($parsed['html'], '<a href="https://ericgitonga.com">link</a>'), 'Renders link');
});

it('BlogService loads markdown posts from content/blog', function () {
    $posts = BlogService::getAllPosts();
    assertTrue(count($posts) >= 1, 'Should find at least 1 post');
    $first = $posts[0];
    assertTrue(!empty($first['title']), 'Post title should be non-empty');
    assertTrue(!empty($first['slug']), 'Post slug should be non-empty');
    $bySlug = BlogService::getPost($first['slug']);
    assertEquals($first['title'], $bySlug['title'], 'getPost should return matching post');
});

// ── 3. DudusCatalogService ──────────────────────────────────────────────────
it('DudusCatalogService ingests card_index.json and normalizes species data', function () {
    $all = DudusCatalogService::getAll();
    assertTrue(count($all) >= 20, 'Should load at least 20 species cards from card_index.json');
    $sample = $all[0];
    assertTrue(!empty($sample['id']), 'Dudu should have an id');
    assertTrue(!empty($sample['common_name']), 'Dudu should have a common name');
    assertTrue(!empty($sample['order']), 'Dudu should have an order');
    assertTrue(!empty($sample['blurb']), 'Dudu should have blurb content');

    // Test getById
    $found = DudusCatalogService::getById($sample['id']);
    assertEquals($sample['common_name'], $found['common_name']);

    // Test getByOrder
    $inOrder = DudusCatalogService::getByOrder($sample['order']);
    assertTrue(count($inOrder) >= 1, 'Should find species in order');

    // Test search
    $results = DudusCatalogService::search($sample['common_name']);
    assertTrue(count($results) >= 1, 'Search by common name should return results');
});

// ── 4. ShopService & Pricing Matrix ─────────────────────────────────────────
it('ShopService enforces 30% discount on non-Dudu collections', function () {
    $catalogue = ShopService::loadCatalogue();
    assertTrue(count($catalogue) >= 10, 'Catalogue should load photos');

    // Find a Dudu photo and a Daguerreotypes photo
    $duduPhoto = null;
    $dagPhoto = null;
    foreach ($catalogue as $p) {
        if ($p['collection'] === 'dudu' && $duduPhoto === null) $duduPhoto = $p;
        if ($p['collection'] === 'daguerreotypes' && $dagPhoto === null) $dagPhoto = $p;
    }

    assertTrue($duduPhoto !== null, 'Dudu photo should exist in catalogue');
    assertTrue($dagPhoto !== null, 'Daguerreotypes photo should exist in catalogue');

    // Base A4 price is 15,000 KES
    $duduA4Price = ShopService::getPrintPrice($duduPhoto['id'], 'A4');
    assertEquals(15000, $duduA4Price, 'Dudu A4 price should be base price 15,000');

    // Daguerreotypes A4 price is discounted by 30% -> 10,500 KES
    $dagA4Price = ShopService::getPrintPrice($dagPhoto['id'], 'A4');
    assertEquals(10500, $dagA4Price, 'Daguerreotypes A4 price should be 10,500 (30% off 15,000)');

    // Verify sizes table for Daguerreotypes
    $dagSizes = ShopService::getSizesForCollection('daguerreotypes');
    assertEquals(10500, $dagSizes['A4']['price'], 'A4 size price in Daguerreotypes should be 10,500');
    assertEquals(17500, $dagSizes['A3']['price'], 'A3 size price in Daguerreotypes should be 17,500 (30% off 25,000)');
    assertEquals(70000, $dagSizes['A0']['price'], 'A0 size price in Daguerreotypes should be 70,000 (30% off 100,000)');
});

// ── 5. CartService & Price Revalidation ─────────────────────────────────────
it('CartService manages items and revalidates prices against server tables', function () {
    CartService::clear();
    assertEquals(0, CartService::itemCount(), 'Cart should start empty');

    $catalogue = ShopService::loadCatalogue();
    $sample = array_values($catalogue)[0];

    // Add item
    $added = CartService::addItem($sample['id'], 'print', 'A4', 2);
    assertTrue($added, 'Item should be added to cart');
    assertEquals(2, CartService::itemCount(), 'Item count should be 2');

    // Tamper test: attempt to tamper with session price directly
    $_SESSION['cart'][0]['unit_price'] = 10; // Try to set price to 10 KES!
    $revalidated = CartService::getCart();
    assertTrue($revalidated[0]['unit_price'] >= 10500, 'Price must be revalidated back to authentic catalog price');

    // Update quantity
    CartService::updateQty(0, 3);
    assertEquals(3, CartService::itemCount(), 'Quantity should be updated to 3');

    // Remove item
    CartService::removeItem(0);
    assertEquals(0, CartService::itemCount(), 'Cart should be empty after removal');
});

// ── 6. MpesaLedger Anti-Replay Engine ───────────────────────────────────────
it('MpesaLedger validates code format and locks against replay attacks', function () {
    $code = 'QA' . rand(1000000, 9999999) . 'X';
    $res1 = MpesaLedger::checkAndRecord($code, 'customer@example.com', 15000);
    assertTrue($res1['success'], 'First registration of fresh M-Pesa code should succeed');

    // Second registration of exact same code must fail closed
    $res2 = MpesaLedger::checkAndRecord($code, 'other@example.com', 15000);
    assertTrue(!$res2['success'], 'Replay submission of identical M-Pesa code must fail');
    assertTrue(str_contains($res2['error'], 'already been registered'), 'Error must specify replay detection');

    // Invalid format must fail
    $invalid = MpesaLedger::checkAndRecord('123', 'bad@example.com', 1000);
    assertTrue(!$invalid['success'], 'Short/invalid code must be rejected');
});

// ── 7. AntiSpamGate: All 7 Security Layers ──────────────────────────────────
it('AntiSpamGate rejects disguised honeypot submission (Layer 1)', function () {
    $post = [
        'website' => 'http://spambot.example.com', // Filled honeypot!
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'message' => 'Hello there',
    ];
    $res = AntiSpamGate::verifySubmission($post, '192.168.1.100');
    assertTrue(!$res['valid'], 'Honeypot submission must be rejected');
    assertTrue(str_contains($res['error'], 'Automated bot activity'), 'Error should identify honeypot');
});

it('AntiSpamGate rejects submissions completed in under 4 seconds (Layer 2)', function () {
    $token = AntiSpamGate::generateProofToken();
    $post = [
        'website' => '',
        'ts' => time(), // 0 seconds elapsed!
        'salt' => $token['salt'],
        'sig' => $token['signature'],
        '_proof' => 'human_token_12345',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'message' => 'Hello there message',
    ];
    $res = AntiSpamGate::verifySubmission($post, '192.168.1.101');
    assertTrue(!$res['valid'], 'Submission under 4s must be rejected');
    assertTrue(str_contains($res['error'], 'too rapidly'), 'Error should identify rapid submission');
});

it('AntiSpamGate locks signatures against single-use replay attacks (Layer 2)', function () {
    $token = AntiSpamGate::generateProofToken();
    // Simulate 5 seconds elapsed
    $post = [
        'website' => '',
        'ts' => time() - 5,
        'salt' => $token['salt'],
        'sig' => hash_hmac('sha256', (time() - 5) . ':' . $token['salt'], AppConfig::getHmacSecret()),
        '_proof' => 'human_token_valid',
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'message' => 'Genuine inquiry message here.',
    ];
    $res1 = AntiSpamGate::verifySubmission($post, '192.168.1.102');
    assertTrue($res1['valid'], 'First legitimate submission must succeed: ' . $res1['error']);

    // Second submission with exact same signature must be rejected!
    $res2 = AntiSpamGate::verifySubmission($post, '192.168.1.102');
    assertTrue(!$res2['valid'], 'Replay submission of used signature must fail');
    assertTrue(str_contains($res2['error'], 'already used'), 'Error should indicate replay rejection');
});

it('AntiSpamGate enforces heuristic content filters: links in name, spam TLDs, shouting (Layer 4)', function () {
    $token = AntiSpamGate::generateProofToken();
    $basePost = [
        'website' => '',
        'ts' => time() - 6,
        'salt' => $token['salt'],
        'sig' => hash_hmac('sha256', (time() - 6) . ':' . $token['salt'], AppConfig::getHmacSecret()),
        '_proof' => 'human_token_valid',
        'email' => 'test@example.com',
    ];

    // Link in name
    $p1 = array_merge($basePost, ['name' => 'Check https://spam.com', 'message' => 'Normal message']);
    $r1 = AntiSpamGate::verifySubmission($p1, '192.168.1.103');
    assertTrue(!$r1['valid'], 'Link in name must be rejected');

    // Multiple links in message (> 1)
    $token2 = AntiSpamGate::generateProofToken();
    $p2 = array_merge($basePost, [
        'ts' => time() - 6,
        'salt' => $token2['salt'],
        'sig' => hash_hmac('sha256', (time() - 6) . ':' . $token2['salt'], AppConfig::getHmacSecret()),
        'name' => 'Valid Name',
        'message' => 'See https://link1.com and also https://link2.com'
    ]);
    $r2 = AntiSpamGate::verifySubmission($p2, '192.168.1.104');
    assertTrue(!$r2['valid'], 'Multiple links in message must be rejected');

    // ALL-CAPS shouting (>60%)
    $token3 = AntiSpamGate::generateProofToken();
    $p3 = array_merge($basePost, [
        'ts' => time() - 6,
        'salt' => $token3['salt'],
        'sig' => hash_hmac('sha256', (time() - 6) . ':' . $token3['salt'], AppConfig::getHmacSecret()),
        'name' => 'Valid Name',
        'message' => 'BUY OUR CRYPTO NOW THIS IS AN URGENT SPECIAL DEAL'
    ]);
    $r3 = AntiSpamGate::verifySubmission($p3, '192.168.1.105');
    assertTrue(!$r3['valid'], 'Excessive uppercase shouting must be rejected');
});

it('AntiSpamGate sanitizes email headers against injection (Layer 6)', function () {
    $injected = "John Doe\r\nBcc: victim@example.com\n<script>";
    $clean = AntiSpamGate::sanitizeHeader($injected);
    assertTrue(!str_contains($clean, "\r"), 'Must strip carriage returns');
    assertTrue(!str_contains($clean, "\n"), 'Must strip newlines');
    assertTrue(!str_contains($clean, "<"), 'Must strip brackets');
});

// ── 8. Router Dispatching ───────────────────────────────────────────────────
it('Router dispatches static and dynamic routes', function () {
    $router = new Router();
    $matched = false;
    $extractedSlug = '';

    $router->get('/blog/{slug}', function ($params) use (&$matched, &$extractedSlug) {
        $matched = true;
        $extractedSlug = $params['slug'];
    });

    $router->dispatch('GET', '/blog/my-awesome-post');
    assertTrue($matched, 'Route should match');
    assertEquals('my-awesome-post', $extractedSlug, 'Dynamic param should be extracted correctly');
});

echo "\n────────────────────────────────────────────────────────────\n";
echo "Tests Passed: {$passed} | Tests Failed: {$failed}\n";
echo "────────────────────────────────────────────────────────────\n";

if ($failed > 0) {
    exit(1);
}
