<?php
namespace Core;

class Request
{

    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    public static function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    // Sanitized POST input
 public static function post(string $key, mixed $default = null): mixed
{
    $val = $_POST[$key] ?? $default;
    return is_string($val) ? trim($val) : $val;
}


    // Sanitized GET input
    public static function get(string $key, mixed $default = null): mixed
    {
        $val = $_GET[$key] ?? $default;
        return is_string($val) ? trim(htmlspecialchars($val, ENT_QUOTES, 'UTF-8')) : $val;
    }

    // Raw POST (for JSON bodies e.g. webhooks)
    public static function raw(): string
    {
        return file_get_contents('php://input');
    }

    // JSON body
    public static function json(): array
    {
        return json_decode(self::raw(), true) ?? [];
    }

    public static function ip(): string
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP']   // Cloudflare real IP
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }
}
