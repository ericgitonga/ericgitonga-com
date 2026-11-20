<?php
declare(strict_types=1);

namespace EricGitonga\Services;

final class CartService
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            $sessionDir = sys_get_temp_dir() . '/ericgitonga_sessions';
            if (!is_dir($sessionDir)) {
                @mkdir($sessionDir, 0775, true);
            }
            if (is_writable($sessionDir)) {
                session_save_path($sessionDir);
            }
            @session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
        }
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public static function getCart(): array
    {
        self::startSession();
        return self::revalidateCart($_SESSION['cart'] ?? []);
    }

    public static function addItem(string $photoId, string $productType, string $size, int $qty): bool
    {
        self::startSession();
        if ($qty <= 0) {
            return false;
        }

        $price = ShopService::getPrintPrice($photoId, $size);
        if ($price === null) {
            return false;
        }

        $photo = ShopService::getPhoto($photoId);
        if ($photo === null) {
            return false;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if item already in cart
        foreach ($_SESSION['cart'] as $i => $item) {
            if ($item['photo_id'] === $photoId && $item['size'] === $size && $item['product_type'] === $productType) {
                $_SESSION['cart'][$i]['qty'] += $qty;
                return true;
            }
        }

        $_SESSION['cart'][] = [
            'photo_id' => $photoId,
            'product_type' => $productType,
            'size' => $size,
            'qty' => $qty,
            'unit_price' => $price,
            'title' => $photo['title'],
            'thumb' => $photo['thumb_sm'],
            'collection' => $photo['collection'],
        ];
        return true;
    }

    public static function updateQty(int $index, int $qty): void
    {
        self::startSession();
        if (!isset($_SESSION['cart'][$index])) {
            return;
        }
        if ($qty <= 0) {
            array_splice($_SESSION['cart'], $index, 1);
        } else {
            $_SESSION['cart'][$index]['qty'] = $qty;
        }
    }

    public static function removeItem(int $index): void
    {
        self::startSession();
        if (isset($_SESSION['cart'][$index])) {
            array_splice($_SESSION['cart'], $index, 1);
        }
    }

    public static function clear(): void
    {
        self::startSession();
        $_SESSION['cart'] = [];
    }

    public static function revalidateCart(array $cart): array
    {
        $valid = [];
        foreach ($cart as $item) {
            $photoId = $item['photo_id'] ?? '';
            $size = $item['size'] ?? '';
            $price = ShopService::getPrintPrice($photoId, $size);
            if ($price !== null) {
                $item['unit_price'] = $price;
                $item['total_price'] = $price * ($item['qty'] ?? 1);
                $valid[] = $item;
            }
        }
        $_SESSION['cart'] = $valid;
        return $valid;
    }

    public static function totalAmount(): int
    {
        $cart = self::getCart();
        $total = 0;
        foreach ($cart as $item) {
            $total += ($item['unit_price'] * $item['qty']);
        }
        return $total;
    }

    public static function itemCount(): int
    {
        $cart = self::getCart();
        $count = 0;
        foreach ($cart as $item) {
            $count += ($item['qty'] ?? 0);
        }
        return $count;
    }
}
