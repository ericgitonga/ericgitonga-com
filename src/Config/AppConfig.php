<?php
declare(strict_types=1);

namespace EricGitonga\Config;

final class AppConfig
{
    private static bool $envLoaded = false;
    private static ?string $envPath = null;

    public static function loadEnv(?string $path = null): void
    {
        if (self::$envLoaded && $path === null) {
            return;
        }
        $target = $path ?? (dirname(__DIR__, 2) . '/.env');
        self::$envPath = $target;
        if (is_file($target) && is_readable($target)) {
            $lines = file($target, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                        continue;
                    }
                    [$k, $v] = explode('=', $trimmed, 2);
                    $k = trim($k);
                    $v = trim($v);
                    if ((str_starts_with($v, "'") && str_ends_with($v, "'")) ||
                        (str_starts_with($v, '"') && str_ends_with($v, '"'))) {
                        $v = substr($v, 1, -1);
                    }
                    if (getenv($k) === false) {
                        putenv("{$k}={$v}");
                        $_ENV[$k] = $v;
                    }
                }
            }
        }
        self::$envLoaded = true;
    }

    public static function env(string $key, string $default = ''): string
    {
        self::loadEnv();
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return (string)$val;
        }
        return (string)($_ENV[$key] ?? $default);
    }

    public static function isPreview(): bool
    {
        $env = strtolower(self::env('APP_ENV', ''));
        if (in_array($env, ['preview', 'preprod', 'staging'], true)) {
            return true;
        }
        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        return str_starts_with($host, 'preview.');
    }

    public static function isProduction(): bool
    {
        if (self::isPreview() || self::isTesting()) {
            return false;
        }
        return strtolower(self::env('APP_ENV', 'production')) === 'production';
    }

    public static function isTesting(): bool
    {
        return strtolower(self::env('APP_ENV', '')) === 'testing';
    }

    public static function appUrl(): string
    {
        $configured = self::env('APP_URL');
        if ($configured !== '') {
            return rtrim($configured, '/');
        }
        if (self::isPreview()) {
            return 'https://preview.ericgitonga.com';
        }
        return 'https://www.ericgitonga.com';
    }

    public static function baseUrl(string $path = ''): string
    {
        $base = self::appUrl();
        $clean = '/' . ltrim($path, '/');
        return ($clean === '/') ? $base : ($base . $clean);
    }

    public static function mediaBaseUrl(): string
    {
        return rtrim(self::env('MEDIA_BASE_URL', 'https://media.ericgitonga.com'), '/');
    }

    public static function rootDir(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function storageDir(string $sub = ''): string
    {
        $base = self::rootDir() . '/storage';
        return $sub === '' ? $base : ($base . '/' . ltrim($sub, '/'));
    }

    public static function contentDir(string $sub = ''): string
    {
        $base = self::rootDir() . '/content';
        return $sub === '' ? $base : ($base . '/' . ltrim($sub, '/'));
    }

    public static function publicDir(string $sub = ''): string
    {
        $base = self::rootDir() . '/public';
        return $sub === '' ? $base : ($base . '/' . ltrim($sub, '/'));
    }

    public static function isLocal(): bool
    {
        $env = strtolower(self::env('APP_ENV', ''));
        return in_array($env, ['local', 'development', 'dev'], true) || PHP_SAPI === 'cli';
    }

    public static function getHmacSecret(): string
    {
        $secret = self::env('APP_HMAC_SECRET');
        if ($secret !== '') {
            return $secret;
        }
        if (self::isProduction()) {
            throw new \RuntimeException("APP_HMAC_SECRET must be configured in production.");
        }
        return 'dev_insecure_hmac_secret_key_ericgitonga_2026';
    }
}
