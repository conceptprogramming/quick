<?php
namespace Middleware;

use Core\Response;

class CSRFMiddleware
{

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!self::validate($token)) {
                Response::error('Invalid CSRF token.', 403);
            }
        }
    }

    // Generate & store token
    public static function generate(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Validate submitted token
    public static function validate(string $token): bool
    {
        return isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function verify(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!self::validate($token)) {
            Response::error('Invalid CSRF token.', 403);
        }
    }

    // HTML hidden input helper
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::generate() . '">';
    }
}
