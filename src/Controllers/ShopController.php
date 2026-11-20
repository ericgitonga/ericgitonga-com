<?php
declare(strict_types=1);

namespace EricGitonga\Controllers;

use EricGitonga\Services\ShopService;
use EricGitonga\Services\CartService;
use EricGitonga\Services\MpesaLedger;
use EricGitonga\Services\Mailer;
use EricGitonga\Security\AntiSpamGate;

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
        $items = CartService::getCart();
        $total = CartService::totalAmount();

        if (empty($items) || $total <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Your cart is empty.']);
            return;
        }

        $customerName = AntiSpamGate::sanitizeHeader((string)($_POST['name'] ?? ''));
        $customerEmail = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $customerPhone = AntiSpamGate::sanitizeHeader((string)($_POST['phone'] ?? ''));
        $mpesaCode = trim((string)($_POST['mpesa_code'] ?? ''));
        $address = trim((string)($_POST['delivery_address'] ?? ''));

        if ($customerName === '' || !$customerEmail || $customerPhone === '' || $mpesaCode === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please provide all required fields including your M-Pesa confirmation code.']);
            return;
        }

        // Anti-replay check & record
        $record = MpesaLedger::checkAndRecord($mpesaCode, $customerEmail, $total);
        if (!$record['success']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $record['error']]);
            return;
        }

        // Build order details
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

        // Send notification to Eric
        $subject = "NEW ORDER: KES " . number_format($total) . " from {$customerName} (M-Pesa: {$record['clean_code']})";
        $body = "A new print shop order has been placed!\n\n" .
                "Customer: {$customerName}\n" .
                "Email: {$customerEmail}\n" .
                "Phone: {$customerPhone}\n" .
                "M-Pesa Code: {$record['clean_code']}\n" .
                "Delivery Address: {$address}\n\n" .
                "Order Total: KES " . number_format($total) . "\n\n" .
                "Items:\n{$orderSummary}\n\n" .
                "Date: " . date('Y-m-d H:i:s') . "\n";

        Mailer::send('gitonga@gmail.com', $subject, $body, $customerEmail);

        // Clear cart
        CartService::clear();

        echo json_encode([
            'success' => true,
            'message' => 'Thank you! Your order has been placed and payment registered. We will confirm shortly via email.',
        ]);
    }
}
