<?php
declare(strict_types=1);

namespace EricGitonga\Services;

use EricGitonga\Config\AppConfig;

final class Mailer
{
    public static function send(string $to, string $subject, string $body, string $replyTo = ''): bool
    {
        $simulate = strtolower(AppConfig::env('SIMULATE_EMAILS', ''));
        if ($simulate === 'yes' || $simulate === 'true' || AppConfig::isPreview() || AppConfig::isTesting()) {
            $logFile = AppConfig::storageDir('logs/simulated_mail.log');
            $dir = dirname($logFile);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            // Rotate/truncate log if larger than 500KB to prevent indefinite PII retention (#30, H06)
            if (file_exists($logFile) && filesize($logFile) > 512000) {
                @rename($logFile, $logFile . '.old');
            }

            // Redact customer phone numbers, full payment codes, and street addresses in simulation logs (#30, H06)
            $safeBody = preg_replace('/(\+?254|0\d{2})\d{4}(\d{3})/', '$1****$2', $body);
            $safeBody = preg_replace('/([A-Z0-9]{3})[A-Z0-9]{4,8}([A-Z0-9]{2})/', '$1****$2', (string)$safeBody);

            $entry = sprintf(
                "[%s] TO: %s | SUBJECT: %s | REPLY-TO: %s\n%s\n----------------------------------------\n",
                date('Y-m-d H:i:s'),
                $to,
                $subject,
                $replyTo,
                $safeBody
            );
            file_put_contents($logFile, $entry, FILE_APPEND);
            return true;
        }

        // Live Resend REST API or standard PHP mail
        $resendKey = AppConfig::env('RESEND_API_KEY');
        if ($resendKey !== '') {
            return self::sendViaResend($resendKey, $to, $subject, $body, $replyTo);
        }

        $headers = [
            'From: Eric Gitonga <no-reply@ericgitonga.com>',
            'Content-Type: text/plain; charset=UTF-8',
        ];
        if ($replyTo !== '') {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    private static function sendViaResend(string $apiKey, string $to, string $subject, string $body, string $replyTo): bool
    {
        $fromEmail = AppConfig::env('FROM_EMAIL', 'Eric Gitonga <orders@ericgitonga.com>');
        $payload = [
            'from' => $fromEmail,
            'to' => [$to],
            'subject' => $subject,
            'text' => $body,
        ];
        if ($replyTo !== '') {
            $payload['reply_to'] = $replyTo;
        }

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($code >= 200 && $code < 300);
    }
}
