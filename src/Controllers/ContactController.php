<?php
declare(strict_types=1);

namespace EricGitonga\Controllers;

use EricGitonga\Security\AntiSpamGate;
use EricGitonga\Services\Mailer;

final class ContactController
{
    public function submit(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $verify = AntiSpamGate::verifySubmission($_POST, $ip);
        if (!$verify['valid']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $verify['error']]);
            return;
        }

        $consent = (string)($_POST['dpa_consent'] ?? '');
        if ($consent !== 'yes' && $consent !== '1' && $consent !== 'true' && $consent !== 'on') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please accept the data protection notice to submit your message.']);
            return;
        }

        $name = AntiSpamGate::sanitizeHeader((string)($_POST['name'] ?? ''));
        $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $message = trim((string)($_POST['message'] ?? ''));

        if ($name === '' || !$email || $message === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Please provide your name, a valid email, and a message.']);
            return;
        }

        $subject = "Message from {$name} via ericgitonga.com";
        // Omit visitor raw IP to comply with Kenya DPA 2019 data minimization principles (#30, H06)
        $body = "Name: {$name}\nEmail: {$email}\nDate: " . date('Y-m-d H:i:s') . "\n\nMessage:\n{$message}";

        $sent = Mailer::send('gitonga@gmail.com', $subject, $body, $email);
        if ($sent) {
            echo json_encode(['success' => true, 'message' => 'Thank you — message received!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to deliver message. Please email directly.']);
        }
    }
}
