<?php
declare(strict_types=1);

namespace EricGitonga\Services;

use EricGitonga\Config\AppConfig;
use EricGitonga\Config\Database;

final class MpesaLedger
{
    public static function checkAndRecord(string $code, string $email, int $amount): array
    {
        $clean = strtoupper(trim($code));
        if (!preg_match('/^[A-Z0-9]{8,15}$/', $clean)) {
            return ['success' => false, 'error' => 'Invalid M-Pesa confirmation code format'];
        }

        $hash = hash_hmac('sha256', $clean, AppConfig::getHmacSecret());
        $db = Database::getPdo();

        $check = $db->prepare("SELECT 1 FROM mpesa_codes WHERE code_hash = ?");
        $check->execute([$hash]);
        if ($check->fetchColumn()) {
            return ['success' => false, 'error' => 'This M-Pesa transaction code has already been registered'];
        }

        $preview = substr($clean, 0, 3) . '***' . substr($clean, -2);
        $ins = $db->prepare("INSERT INTO mpesa_codes (code_hash, code_preview, customer_email, amount, created_at)
            VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$hash, $preview, $email, $amount, time()]);

        return ['success' => true, 'clean_code' => $clean, 'code_preview' => $preview];
    }
}
