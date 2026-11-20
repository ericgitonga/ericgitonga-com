<?php
declare(strict_types=1);

namespace EricGitonga;

use EricGitonga\Config\AppConfig;

spl_autoload_register(function (string $class) {
    $prefix = 'EricGitonga\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

AppConfig::loadEnv();

// Set error handling
if (AppConfig::isProduction()) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Configure writable session path
$sessionDir = sys_get_temp_dir() . '/ericgitonga_sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0775, true);
}
if (is_writable($sessionDir) && session_status() === PHP_SESSION_NONE) {
    session_save_path($sessionDir);
}

// Start cart session if not already active and not in testing/CLI without session
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE && !headers_sent()) {
    \EricGitonga\Services\CartService::startSession();
}
