<?php
declare(strict_types=1);

namespace EricGitonga\Controllers;

use EricGitonga\Services\ShopService;
use EricGitonga\Services\CartService;
use EricGitonga\Services\MpesaLedger;
use EricGitonga\Services\Mailer;
use EricGitonga\Security\AntiSpamGate;
use EricGitonga\Config\Database;

final class ShopController
{
    public function index(): void
    {
        $title = 'Shop — Archival Prints & Merchandise — Eric Gitonga';
        $description = 'Fine art archival prints, apparel, and coasters from Eric Gitonga\'s photography and entomology collections.';
        $currentSection = 'shop';

        ob_start();
        require dirname(__DIR__) . '/Views/shop/index.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function prints(): void
    {
        $title = 'Dudu Prints — Fine Art Archival Prints — Eric Gitonga';
        $description = 'Museum-grade archival macro insect photography prints, with live living-room wall scaling.';
        $currentSection = 'shop';
        $collection = 'dudu';
        $photos = ShopService::getPhotosForCollection('dudu');
        $sizes = ShopService::getSizesForCollection('dudu');

        ob_start();
        require dirname(__DIR__) . '/Views/shop/prints.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function daguerreotypes(): void
    {
        $title = 'Daguerreotypes Prints — Landscapes & Portraits — Eric Gitonga';
        $description = 'Fine art archival landscape and portrait prints from the Daguerreotypes collection.';
        $currentSection = 'shop';
        $collection = 'daguerreotypes';
        $photos = ShopService::getPhotosForCollection('daguerreotypes');
        $sizes = ShopService::getSizesForCollection('daguerreotypes');

        ob_start();
        require dirname(__DIR__) . '/Views/shop/daguerreotypes.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function apparel(): void
    {
        $title = 'Apparel — Dudu T-Shirts — Eric Gitonga';
        $description = 'Screen-printed heavy cotton t-shirts featuring Kenyan entomology artwork.';
        $currentSection = 'shop';

        ob_start();
        require dirname(__DIR__) . '/Views/shop/apparel.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function coasters(): void
    {
        $title = 'Coasters — Dudu Ceramic Coasters — Eric Gitonga';
        $description = 'Set of 4 cork-backed ceramic macro photo coasters.';
        $currentSection = 'shop';

        ob_start();
        require dirname(__DIR__) . '/Views/shop/coasters.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function cart(): void
    {
        $title = 'Cart — Eric Gitonga Shop';
        $description = 'Your selected archival prints and merchandise.';
        $currentSection = 'shop';
        $items = CartService::getCart();
        $total = CartService::totalAmount();

        ob_start();
        require dirname(__DIR__) . '/Views/shop/cart.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function cartAdd(): void
    {
        header('Content-Type: application/json');
        $photoId = trim((string)($_POST['photo_id'] ?? ''));
        $size = trim((string)($_POST['size'] ?? 'A4'));
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $type = trim((string)($_POST['product_type'] ?? 'print'));

        $success = CartService::addItem($photoId, $type, $size, $qty);
        echo json_encode([
            'success' => $success,
            'item_count' => CartService::itemCount(),
            'total' => CartService::totalAmount(),
        ]);
    }

    public function cartUpdate(): void
    {
        header('Content-Type: application/json');
        $index = (int)($_POST['index'] ?? -1);
        $qty = (int)($_POST['qty'] ?? 1);
        CartService::updateQty($index, $qty);
        echo json_encode([
            'success' => true,
            'item_count' => CartService::itemCount(),
            'total' => CartService::totalAmount(),
        ]);
    }

    public function cartRemove(): void
    {
        header('Content-Type: application/json');
        $index = (int)($_POST['index'] ?? -1);
        CartService::removeItem($index);
        echo json_encode([
            'success' => true,
            'item_count' => CartService::itemCount(),
            'total' => CartService::totalAmount(),
        ]);
    }

    public function checkout(): void
    {
        $title = 'Checkout — M-Pesa Payment — Eric Gitonga Shop';
        $description = 'Complete your order via M-Pesa.';
        $currentSection = 'shop';
        $items = CartService::getCart();
        $total = CartService::totalAmount();

        if (empty($items)) {
            header('Location: /shop/cart');
            exit;
        }

        ob_start();
        require dirname(__DIR__) . '/Views/shop/checkout.php';
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/base.php';
    }

    public function checkoutSubmit(): void
    {
        header('Content-Type: application/json');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $items = CartService::getCart();
        $total = CartService::totalAmount();

        if (empty($items) || $total <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Your cart is empty.']);
            return;
        }

        // Layer 1-5: Anti-Spam Gate Verification (#28, H03)
        $clientIp = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $verify = AntiSpamGate::verifySubmission($_POST, $clientIp);
        if (!$verify['valid']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $verify['error']]);
            return;
        }

        // Kenya DPA 2019 Section 29 & 32 Consent Verification (#30, H05)
        $consent = (string)($_POST['dpa_consent'] ?? '');
        if ($consent !== 'yes' && $consent !== '1' && $consent !== 'true' && $consent !== 'on') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please accept the data protection notice to proceed with checkout.']);
            return;
        }

        $customerName = AntiSpamGate::sanitizeHeader((string)($_POST['name'] ?? ''));
        $customerEmail = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $customerPhone = AntiSpamGate::sanitizeHeader((string)($_POST['phone'] ?? ''));
        $mpesaCode = strtoupper(trim((string)($_POST['mpesa_code'] ?? '')));
        $address = trim((string)($_POST['delivery_address'] ?? ''));

        if ($customerName === '' || !$customerEmail || $customerPhone === '' || $mpesaCode === '' || $address === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please provide all required fields including delivery address and M-Pesa reference.']);
            return;
        }

        if (strlen($customerName) > 120 || strlen($customerPhone) > 40 || strlen($mpesaCode) > 20 || strlen($address) > 1000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Input exceeds maximum allowed length.']);
            return;
        }

        // M-Pesa payment deduplication and registration (#27, H01)
        $record = MpesaLedger::checkAndRecord($mpesaCode, $customerEmail, $total);
        if (!$record['success']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $record['error']]);
            return;
        }

        $codePreview = $record['code_preview'] ?? (substr($mpesaCode, 0, 3) . '***' . substr($mpesaCode, -2));
        $orderId = 'ord_' . bin2hex(random_bytes(6));
        $now = time();

        // Atomically persist order record in database (#27, H02)
        try {
            $db = Database::getPdo();
            $stmt = $db->prepare("INSERT INTO orders (order_id, customer_name, customer_email, customer_phone, mpesa_code_preview, amount, status, delivery_address, items_json, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'pending_verification', ?, ?, ?)");
            $stmt->execute([
                $orderId,
                $customerName,
                $customerEmail,
                $customerPhone,
                $codePreview,
                $total,
                $address,
                json_encode($items, JSON_UNESCAPED_SLASHES),
                $now,
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error registering order. Please contact support.']);
            return;
        }

        // Build order details for notification
        $orderLines = [];
        foreach ($items as $item) {
            $orderLines[] = sprintf("- %s (%s, %s): %d x KES %s = KES %s",
                $item['title'],
                $item['product_type'],
                $item['size'],
                $item['qty'],
                number_format($item['unit_price']),
                number_format($item['total_price'])
            );
        }
        $orderSummary = implode("\n", $orderLines);

        // Send order notification without broadcasting full payment reference in subject line (#30, H06)
        $subject = "NEW ORDER #{$orderId}: KES " . number_format($total) . " from {$customerName}";
        $body = "A new print shop order has been placed!\n\n" .
                "Order ID: {$orderId}\n" .
                "Customer: {$customerName}\n" .
                "Email: {$customerEmail}\n" .
                "Phone: {$customerPhone}\n" .
                "Payment Ref (preview): {$codePreview}\n" .
                "Payment Status: Pending Verification\n" .
                "Delivery Address: {$address}\n\n" .
                "Order Total: KES " . number_format($total) . "\n\n" .
                "Items:\n{$orderSummary}\n\n" .
                "Placed At: " . date('Y-m-d H:i:s', $now) . " EAT\n";

        Mailer::send('gitonga@gmail.com', $subject, $body, $customerEmail);

        // Clear cart
        CartService::clear();

        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'message' => "Thank you, {$customerName}! Order #{$orderId} placed. Your payment reference ({$codePreview}) has been recorded for verification. We will send a confirmation email shortly.",
        ]);
    }
}
