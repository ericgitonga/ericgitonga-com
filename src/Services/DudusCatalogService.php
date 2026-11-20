<?php
declare(strict_types=1);

namespace EricGitonga\Services;

use EricGitonga\Config\AppConfig;

final class DudusCatalogService
{
    private static ?array $cards = null;
    private static ?array $orders = null;

    public static function load(): array
    {
        if (self::$cards !== null) {
            return self::$cards;
        }
        $path = AppConfig::contentDir('dudus/card_index.json');
        if (!is_file($path)) {
            self::$cards = [];
            self::$orders = [];
            return self::$cards;
        }
        $raw = (string)file_get_contents($path);
        $data = json_decode($raw, true) ?: [];

        $items = isset($data['cards']) ? $data['cards'] : (is_array($data) ? $data : []);
        $cards = [];
        $ordersMap = [];

        foreach ($items as $item) {
            $id = (string)($item['id'] ?? $item['slug'] ?? '');
            if ($id === '') {
                continue;
            }
            $order = (string)($item['order'] ?? 'Other');
            $blurb = (string)($item['blurb'] ?? $item['description'] ?? '');
            if ($blurb === '' && !empty($item['sections']) && is_array($item['sections'])) {
                $blurb = (string)($item['sections'][0]['body'] ?? '');
            }

            $cards[$id] = [
                'id' => $id,
                'common_name' => $item['common_name'] ?? $item['title'] ?? 'Unknown Dudu',
                'order' => $order,
                'family' => $item['family'] ?? '',
                'scientific_name' => $item['scientific_name'] ?? '',
                'size' => $item['size'] ?? '',
                'diet' => $item['diet'] ?? '',
                'habitat' => $item['habitat'] ?? 'Kenya',
                'blurb' => $blurb,
                'image_url' => $item['image_url'] ?? $item['image'] ?? '',
                'plate_id' => $item['plate_id'] ?? '',
            ];
            $ordersMap[$order] = ($ordersMap[$order] ?? 0) + 1;
        }

        self::$cards = $cards;
        self::$orders = $ordersMap;
        return self::$cards;
    }

    public static function getAll(): array
    {
        return array_values(self::load());
    }

    public static function getById(string $id): ?array
    {
        $cards = self::load();
        return $cards[$id] ?? null;
    }

    public static function getByOrder(string $order): array
    {
        $cards = self::load();
        $filtered = [];
        $target = strtolower($order);
        foreach ($cards as $card) {
            if (strtolower($card['order']) === $target) {
                $filtered[] = $card;
            }
        }
        return $filtered;
    }

    public static function getOrders(): array
    {
        self::load();
        return self::$orders ?? [];
    }

    public static function search(string $query): array
    {
        $q = trim(strtolower($query));
        if ($q === '') {
            return self::getAll();
        }
        $cards = self::load();
        $matches = [];
        foreach ($cards as $c) {
            $haystack = strtolower(
                $c['common_name'] . ' ' .
                $c['order'] . ' ' .
                $c['family'] . ' ' .
                $c['blurb'] . ' ' .
                $c['habitat']
            );
            if (str_contains($haystack, $q)) {
                $matches[] = $c;
            }
        }
        return $matches;
    }
}
