<?php
declare(strict_types=1);

namespace EricGitonga\Security;

use EricGitonga\Config\AppConfig;
use EricGitonga\Config\Database;

final class AntiSpamGate
{
    public const MIN_SUBMISSION_SECONDS = 4;
    public const MAX_SUBMISSION_SECONDS = 7200; // 2 hours

    public static function generateProofToken(): array
    {
        $now = time();
        $salt = bin2hex(random_bytes(8));
        $secret = AppConfig::getHmacSecret();
        $raw = "{$now}:{$salt}";
        $sig = hash_hmac('sha256', $raw, $secret);
        return [
            'timestamp' => $now,
            'salt' => $salt,
            'signature' => $sig,
        ];
    }

    public static function verifySubmission(array $post, string $clientIp): array
    {
        // Layer 1: Disguised Text Honeypot (website)
        $hp = trim((string)($post['website'] ?? ''));
        if ($hp !== '') {
            return ['valid' => false, 'error' => 'Automated bot activity detected (hp)'];
        }

        // Layer 2: Signed Timestamp Time-Trap & Single-Use Replay Lock
        $ts = (int)($post['ts'] ?? 0);
        $salt = (string)($post['salt'] ?? '');
        $sig = (string)($post['sig'] ?? '');
        $now = time();

        if ($ts <= 0 || $salt === '' || $sig === '') {
            return ['valid' => false, 'error' => 'Missing security verification tokens'];
        }

        $expectedSig = hash_hmac('sha256', "{$ts}:{$salt}", AppConfig::getHmacSecret());
        if (!hash_equals($expectedSig, $sig)) {
            return ['valid' => false, 'error' => 'Invalid security signature'];
        }

        $elapsed = $now - $ts;
        if ($elapsed < self::MIN_SUBMISSION_SECONDS) {
            return ['valid' => false, 'error' => 'Form submitted too rapidly'];
        }
        if ($elapsed > self::MAX_SUBMISSION_SECONDS) {
            return ['valid' => false, 'error' => 'Form submission token expired'];
        }

        $db = Database::getPdo();
        // Check single-use replay
        $stmt = $db->prepare("SELECT 1 FROM used_signatures WHERE sig = ?");
        $stmt->execute([$sig]);
        if ($stmt->fetchColumn()) {
            return ['valid' => false, 'error' => 'Security token already used (replay rejected)'];
        }

        // Lock signature immediately
        $ins = $db->prepare("INSERT INTO used_signatures (sig, created_at) VALUES (?, ?)");
        $ins->execute([$sig, $now]);

        // Cleanup old signatures older than 24h
        if (random_int(1, 50) === 1) {
            $db->exec("DELETE FROM used_signatures WHERE created_at < " . ($now - 86400));
        }

        // Layer 3: Client-side interaction token (populated via JS)
        $proofToken = (string)($post['_proof'] ?? '');
        if ($proofToken === '' || !str_starts_with($proofToken, 'human_')) {
            return ['valid' => false, 'error' => 'Missing client interaction confirmation'];
        }

        // Layer 4: Heuristic Content & URL Filter
        $name = (string)($post['name'] ?? '');
        $email = (string)($post['email'] ?? '');
        $message = (string)($post['message'] ?? '');

        if (preg_match('~https?://|www\.~i', $name)) {
            return ['valid' => false, 'error' => 'Links are not permitted in the Name field'];
        }

        // Cap URLs in message <= 1
        $urlCount = preg_match_all('~https?://[^\s]+~i', $message, $m);
        if ($urlCount > 1) {
            return ['valid' => false, 'error' => 'Messages are limited to at most one link'];
        }

        // Block spam shorteners/tlds
        $blockedTlds = ['\.ru', '\.xyz', '\.top', '\.click', 'bit\.ly', 't\.me', 'telegra\.ph'];
        foreach ($blockedTlds as $tld) {
            if (preg_match('~' . $tld . '~i', $message)) {
                return ['valid' => false, 'error' => 'Message contains disallowed links or domains'];
            }
        }

        // Shouting detection: > 60% uppercase across >= 15 letters
        $letters = preg_replace('/[^a-zA-Z]/', '', $message);
        if (strlen($letters) >= 15) {
            $upper = preg_replace('/[^A-Z]/', '', $letters);
            if ((strlen($upper) / strlen($letters)) > 0.60) {
                return ['valid' => false, 'error' => 'Message rejected due to excessive capitalization'];
            }
        }

        // CamelCase bot names (e.g. EdwardWaw)
        if (preg_match('/^[A-Z][a-z]+[A-Z][a-z]+$/', trim($name))) {
            return ['valid' => false, 'error' => 'Invalid name format'];
        }

        // Layer 5: Circuit-breaker Rate Limiting (per-IP <= 2/15min, <= 5/24h, global <= 15/1h per GEMINI.md)
        $ipHash = hash_hmac('sha256', $clientIp, AppConfig::getHmacSecret());
        $currentHour = (int)floor($now / 3600);
        $window15m = (int)floor($now / 900);
        $key15m = substr($ipHash, 0, 48) . '_15m';

        // 1. Check per-IP 15-minute rate limit (<= 2 per 15 mins)
        $stmt15 = $db->prepare("SELECT count FROM spam_rate_limits WHERE ip_hash = ? AND window_hour = ?");
        $stmt15->execute([$key15m, $window15m]);
        if ((int)($stmt15->fetchColumn() ?: 0) >= 2) {
            return ['valid' => false, 'error' => 'Rate limit exceeded (too many attempts in 15 minutes). Please try again later.'];
        }

        // 2. Check per-IP hourly/daily rate limit (<= 5)
        $limitStmt = $db->prepare("SELECT count FROM spam_rate_limits WHERE ip_hash = ? AND window_hour = ?");
        $limitStmt->execute([$ipHash, $currentHour]);
        $ipCount = (int)($limitStmt->fetchColumn() ?: 0);
        if ($ipCount >= 5) {
            return ['valid' => false, 'error' => 'Rate limit exceeded. Please try again later.'];
        }

        // 3. Global circuit-breaker check (<= 15 per hour across all visitors)
        $globStmt = $db->prepare("SELECT SUM(count) FROM spam_rate_limits WHERE window_hour = ? AND ip_hash NOT LIKE '%_15m'");
        $globStmt->execute([$currentHour]);
        $globalCount = (int)($globStmt->fetchColumn() ?: 0);
        if ($globalCount >= 15) {
            return ['valid' => false, 'error' => 'Service is busy. Please try again in an hour.'];
        }

        // Increment counts
        $driver = $db->getAttribute(\PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $inc = $db->prepare("INSERT INTO spam_rate_limits (ip_hash, window_hour, count) VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE count = count + 1");
        } else {
            $inc = $db->prepare("INSERT INTO spam_rate_limits (ip_hash, window_hour, count) VALUES (?, ?, 1)
                ON CONFLICT(ip_hash, window_hour) DO UPDATE SET count = count + 1");
        }
        $inc->execute([$ipHash, $currentHour]);
        $inc->execute([$key15m, $window15m]);

        return ['valid' => true, 'error' => ''];
    }

    public static function sanitizeHeader(string $val): string
    {
        // Layer 6: Strip CRLF, brackets, and quotes from email headers
        return preg_replace('/[\r\n<>",;]/', '', trim($val));
    }
}
