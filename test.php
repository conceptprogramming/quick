<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>✅ PHP is working</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// ── Test 1: ENV file ──────────────────────────────────────
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "<p>✅ <strong>.env found</strong> at: $envFile</p>";
} else {
    echo "<p>❌ <strong>.env NOT found</strong> at: $envFile</p>";
}

// ── Test 2: Load ENV ──────────────────────────────────────
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value);
}
echo "<p>✅ <strong>ENV loaded.</strong> APP_URL = " . htmlspecialchars($env['APP_URL'] ?? 'NOT SET') . "</p>";

// ── Test 3: Database ──────────────────────────────────────
$host = $env['DB_HOST'] ?? '';
$name = $env['DB_NAME'] ?? '';
$user = $env['DB_USER'] ?? '';
$pass = $env['DB_PASS'] ?? '';

echo "<p><strong>DB_HOST:</strong> $host | <strong>DB_NAME:</strong> $name | <strong>DB_USER:</strong> $user</p>";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$name;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p>✅ <strong>Database connected</strong></p>";

    // Count users
    $st = $pdo->query("SELECT COUNT(*) FROM users");
    echo "<p>✅ <strong>Users table OK.</strong> Total users: " . $st->fetchColumn() . "</p>";

} catch (Exception $e) {
    echo "<p>❌ <strong>Database error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

// ── Test 4: Required extensions ───────────────────────────
$required = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'curl'];
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>" . ($loaded ? '✅' : '❌') . " Extension <strong>$ext</strong>: " . ($loaded ? 'loaded' : 'MISSING') . "</p>";
}

// ── Test 5: File paths ────────────────────────────────────
$paths = [
    'config/app.php'        => __DIR__ . '/config/app.php',
    'config/constants.php'  => __DIR__ . '/config/constants.php',
    'src/Database.php'      => __DIR__ . '/src/Database.php',
    'public/index.php'      => __DIR__ . '/public/index.php',
];
foreach ($paths as $label => $path) {
    $exists = file_exists($path);
    echo "<p>" . ($exists ? '✅' : '❌') . " <strong>$label</strong>: " . ($exists ? 'found' : 'NOT FOUND') . "</p>";
}

// ── Test 6: Session ───────────────────────────────────────
session_start();
$_SESSION['test'] = 'ok';
echo "<p>" . ($_SESSION['test'] === 'ok' ? '✅' : '❌') . " <strong>Sessions</strong> working</p>";

// ── Test 7: mod_rewrite ───────────────────────────────────
echo "<p><strong>DOCUMENT_ROOT:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>SCRIPT_FILENAME:</strong> " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";

echo "<hr><p style='color:red'><strong>⚠️ DELETE this file before going live!</strong></p>";
