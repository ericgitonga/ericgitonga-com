<?php
declare(strict_types=1);

namespace EricGitonga\Services;

use EricGitonga\Config\AppConfig;

final class MpesaLedger
{
    private static ?\PDO $sqlite = null;

    private static function getDb(): \PDO
    {
        if (self::$sqlite !== null) {
            return self::$sqlite;
        }
        $dbPath = AppConfig::storageDir('database.sqlite');
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        self::$sqlite = new \PDO('sqlite:' . $dbPath);
        self::$sqlite->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        self::$sqlite->exec("CREATE TABLE IF NOT EXISTS mpesa_codes (
            code_hash TEXT PRIMARY KEY,
            code_preview TEXT,
            customer_email TEXT,
            amount INTEGER,
            created_at INTEGER
        )");
        return self::$sqlite;
    }

    public static function checkAndRecord(string $code, string $email, int $amount): array
    {
        $clean = strtoupper(trim($code));
        // M-Pesa confirmation code heuristic (typically 10 alphanumeric characters, e.g. QA12BC345D)
        if (!preg_match('/^[A-Z0-9]{8,15}$/', $clean)) {
            return ['success' => false, 'error' => 'Invalid M-Pesa confirmation code format'];
        }

        $hash = hash_hmac('sha256', $clean, AppConfig::getHmacSecret());
        $db = self::getDb();

        $check = $db->prepare("SELECT 1 FROM mpesa_codes WHERE code_hash = ?");
        $check->execute([$hash]);
        if ($check->fetchColumn()) {
            return ['success' => false, 'error' => 'This M-Pesa transaction code has already been registered'];
        }

        $preview = substr($clean, 0, 3) . '***' . substr($clean, -2);
        $ins = $db->prepare("INSERT INTO mpesa_codes (code_hash, code_preview, customer_email, amount, created_at)
            VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$hash, $preview, $email, $amount, time()]);

        return ['success' => true, 'clean_code' => $clean];
    }
}
