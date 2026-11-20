<?php
declare(strict_types=1);

namespace EricGitonga\Config;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function getPdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $driver = strtolower(AppConfig::env('DB_DRIVER', ''));
        // In preview and testing, use isolated SQLite (just like for Busherian Biasharas)
        if (AppConfig::isPreview() || AppConfig::isTesting()) {
            $driver = 'sqlite';
        } elseif ($driver === '') {
            $driver = AppConfig::env('DB_NAME') ? 'mysql' : 'sqlite';
        }

        if ($driver === 'mysql') {
            try {
                $host = AppConfig::env('DB_HOST', 'localhost');
                $port = AppConfig::env('DB_PORT', '3306');
                $name = AppConfig::env('DB_NAME', 'egimba_main');
                $user = AppConfig::env('DB_USER', 'egimba_mainuser');
                $pass = AppConfig::env('DB_PASS', '');

                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5,
                ]);
                self::$pdo = $pdo;
                self::initSchema($pdo, 'mysql');
                return self::$pdo;
            } catch (PDOException $e) {
                if (!AppConfig::isProduction()) {
                    error_log("MySQL connection failed, falling back to SQLite: " . $e->getMessage());
                } else {
                    throw $e;
                }
            }
        }

        // Isolated SQLite database
        $sqlitePath = AppConfig::env('DB_SQLITE_PATH');
        if (!$sqlitePath) {
            $sqlitePath = AppConfig::storageDir('database.sqlite');
        }
        $dir = dirname($sqlitePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $pdo = new PDO("sqlite:{$sqlitePath}", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        self::$pdo = $pdo;
        self::initSchema($pdo, 'sqlite');
        return self::$pdo;
    }

    public static function setPdo(?PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    private static function initSchema(PDO $pdo, string $driver): void
    {
        if ($driver === 'mysql') {
            $pdo->exec("CREATE TABLE IF NOT EXISTS used_signatures (
                sig VARCHAR(64) PRIMARY KEY,
                created_at INT UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pdo->exec("CREATE TABLE IF NOT EXISTS spam_rate_limits (
                ip_hash VARCHAR(64) NOT NULL,
                window_hour INT UNSIGNED NOT NULL,
                count INT UNSIGNED NOT NULL,
                PRIMARY KEY (ip_hash, window_hour)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pdo->exec("CREATE TABLE IF NOT EXISTS mpesa_codes (
                code_hash VARCHAR(64) PRIMARY KEY,
                code_preview VARCHAR(32) NOT NULL,
                customer_email VARCHAR(191) NOT NULL,
                amount INT UNSIGNED NOT NULL,
                created_at INT UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else {
            $pdo->exec("CREATE TABLE IF NOT EXISTS used_signatures (
                sig TEXT PRIMARY KEY,
                created_at INTEGER NOT NULL
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS spam_rate_limits (
                ip_hash TEXT NOT NULL,
                window_hour INTEGER NOT NULL,
                count INTEGER NOT NULL,
                PRIMARY KEY (ip_hash, window_hour)
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS mpesa_codes (
                code_hash TEXT PRIMARY KEY,
                code_preview TEXT NOT NULL,
                customer_email TEXT NOT NULL,
                amount INTEGER NOT NULL,
                created_at INTEGER NOT NULL
            )");
        }
    }
}
