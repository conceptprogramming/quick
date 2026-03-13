<?php
namespace Core;

class Response
{

    public static function redirect(string $path): void
    {
        session_write_close(); 
        header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
        exit;
    }

    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function success(array $data = [], string $message = 'OK'): void
    {
        self::json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    public static function error(string $message, int $status = 400): void
    {
        self::json(['success' => false, 'message' => $message], $status);
    }

    public static function view(string $viewPath, array $vars = []): void
    {
        extract($vars);
        require __DIR__ . '/../../views/' . $viewPath . '.php';
    }
}
