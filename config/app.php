<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ── Load .env ─────────────────────────────────────────────────
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '='))
            continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

// ── Load Constants ────────────────────────────────────────────
require_once __DIR__ . '/constants.php';

// ── Load Database ─────────────────────────────────────────────
require_once __DIR__ . '/database.php';

// ── Autoloader ────────────────────────────────────────────────
spl_autoload_register(function (string $class) {
    $base = dirname(__DIR__) . '/src/';
    $file = $base . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file))
        require_once $file;
});

// ── Error Handling ────────────────────────────────────────────
if (($_ENV['APP_ENV'] ?? 'local') === 'local') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ── Session Config ────────────────────────────────────────────
// Derive cookie path from APP_URL — works on both local subdirectory and root domain
$basePath   = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/');
$cookiePath = ($basePath === '') ? '/' : $basePath . '/';
$secure     = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => $cookiePath,
    'httponly' => true,
    'secure'   => $secure,
    'samesite' => 'Strict',
]);

ini_set('session.use_strict_mode', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Timezone ──────────────────────────────────────────────────
date_default_timezone_set('UTC');
