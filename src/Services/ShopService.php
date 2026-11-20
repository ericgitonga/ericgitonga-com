<?php
declare(strict_types=1);

namespace EricGitonga\Services;

use EricGitonga\Config\AppConfig;

final class ShopService
{
    public const NON_DUDU_PRINT_DISCOUNT = 0.30;

    public const PRINT_SIZES = [
        'A4' => ['price' => 15000, 'w_mm' => 210, 'h_mm' => 297],
        'A3' => ['price' => 25000, 'w_mm' => 297, 'h_mm' => 420],
        'A2' => ['price' => 40000, 'w_mm' => 420, 'h_mm' => 594],
        'A1' => ['price' => 70000, 'w_mm' => 594, 'h_mm' => 841],
        'A0' => ['price' => 100000, 'w_mm' => 841, 'h_mm' => 1189],
        'Bookmark' => ['price' => 1500, 'w_mm' => 50, 'h_mm' => 200],
    ];

    public const DUDU_CATEGORIES = [
        'Flies', 'Spiders', 'Beetles', 'Bees', 'Moths', 'Butterflies', 'Ants',
        'Damselflies', 'Dragonflies', 'Mantises', 'Wasps', 'True Bugs',
        'Neuroptera', 'Orthoptera', 'Caterpillars', 'Scorpions', 'Stick Insects', 'Other',
    ];

    public const DAGUERREOTYPES_CATEGORIES = [
        'Beauty In The Ordinary', 'Racecourse', 'Florals',
    ];

    private static ?array $catalogue = null;

    public static function loadCatalogue(): array
    {
        if (self::$catalogue !== null) {
            return self::$catalogue;
        }
        $path = AppConfig::contentDir('shop/catalogue.json');
        if (!is_file($path)) {
            self::$catalogue = [];
            return self::$catalogue;
        }
        $raw = (string)file_get_contents($path);
        $data = json_decode($raw, true) ?: [];
        $images = $data['images'] ?? (is_array($data) ? $data : []);

        $indexed = [];
        foreach ($images as $img) {
            $id = (string)($img['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $cat = (string)($img['category'] ?? 'Other');
            $indexed[$id] = [
                'id' => $id,
                'title' => $img['title'] ?? $id,
                'category' => $cat,
                'collection' => self::getCollectionForCategory($cat),
                'src' => $img['src'] ?? '',
                'thumb' => $img['thumb'] ?? $img['src'] ?? '',
                'thumb_sm' => $img['thumb_sm'] ?? $img['thumb'] ?? $img['src'] ?? '',
                'width' => (int)($img['width'] ?? 1500),
                'height' => (int)($img['height'] ?? 1000),
                'bookmark' => $img['bookmark'] ?? null,
            ];
        }

        self::$catalogue = $indexed;
        return self::$catalogue;
    }

    public static function getCollectionForCategory(string $category): string
    {
        if (in_array($category, self::DAGUERREOTYPES_CATEGORIES, true)) {
            return 'daguerreotypes';
        }
        return 'dudu';
    }

    public static function getPhoto(string $photoId): ?array
    {
        $cat = self::loadCatalogue();
        return $cat[$photoId] ?? null;
    }

    public static function getPhotosForCollection(string $collection): array
    {
        $cat = self::loadCatalogue();
        $res = [];
        foreach ($cat as $p) {
            if ($p['collection'] === $collection) {
                $res[] = $p;
            }
        }
        return $res;
    }

    public static function getPrintPrice(string $photoId, string $size): ?int
    {
        if (!isset(self::PRINT_SIZES[$size])) {
            return null;
        }
        $photo = self::getPhoto($photoId);
        if ($photo === null) {
            return null;
        }
        $base = self::PRINT_SIZES[$size]['price'];
        if ($photo['collection'] === 'dudu') {
            return $base;
        }
        // Non-Dudu collections get 30% discount
        return (int)round($base * (1 - self::NON_DUDU_PRINT_DISCOUNT));
    }

    public static function getSizesForCollection(string $collection): array
    {
        $sizes = [];
        $isDiscounted = ($collection !== 'dudu');
        foreach (self::PRINT_SIZES as $s => $spec) {
            $price = $spec['price'];
            if ($isDiscounted) {
                $price = (int)round($price * (1 - self::NON_DUDU_PRINT_DISCOUNT));
            }
            $sizes[$s] = array_merge($spec, ['price' => $price]);
        }
        return $sizes;
    }
}
