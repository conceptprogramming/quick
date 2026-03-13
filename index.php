<?php
require_once __DIR__ . '/config/app.php';

$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/');
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = '/' . ltrim(str_replace($basePath, '', $uri), '/');
$uri = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        '/' => ['Controllers\AuthController', 'landing'],
        '/login' => ['Controllers\AuthController', 'loginForm'],
        '/verify' => ['Controllers\AuthController', 'verifyForm'],
        '/logout' => ['Controllers\AuthController', 'logout'],
        '/dashboard' => ['Controllers\DashboardController', 'index'],
        '/chat' => ['Controllers\ChatController', 'index'],
        '/summary' => ['Controllers\SummaryController', 'index'],
        '/quiz' => ['Controllers\QuizController', 'index'],
        '/pdf/test' => ['Controllers\PDFController', 'test'],
        '/plans' => ['Controllers\PlansController', 'index'],
        '/profile' => ['Controllers\ProfileController', 'index'],
        '/admin' => ['Controllers\AdminController', 'index'],
        '/admin/user' => ['Controllers\AdminController', 'user'],


    ],
    'POST' => [
        '/login' => ['Controllers\AuthController', 'sendOTP'],
        '/verify' => ['Controllers\AuthController', 'verifyOTP'],
        '/pdf/upload' => ['Controllers\PDFController', 'upload'],
        '/pdf/process' => ['Controllers\PDFController', 'process'],
        '/pdf/chat' => ['Controllers\PDFController', 'chat'],
        '/pdf/summary' => ['Controllers\PDFController', 'summary'],
        '/quiz/mcq' => ['Controllers\QuizController', 'mcq'],
        '/quiz/truefalse' => ['Controllers\QuizController', 'trueFalse'],
        '/payment/confirm' => ['Controllers\PaymentController', 'confirm'],
        '/payment/webhook'  => ['Controllers\PaymentController', 'webhook'], 
        '/admin/login' => ['Controllers\AdminController', 'login'],
        '/admin/logout' => ['Controllers\AdminController', 'adminLogout'],
        '/admin/credits' => ['Controllers\AdminController', 'adjustCredits'],
        '/admin/status' => ['Controllers\AdminController', 'toggleStatus'],
    ],
];

$protected = [
    '/dashboard',
    '/chat',
    '/summary',
    '/qa',
    '/quiz',
    '/pdf/upload',
    '/pdf/process',
    '/pdf/chat',
    '/pdf/summary',
    '/quiz/mcq',
    '/quiz/truefalse',
    '/pdf/test',
    '/plans',
    '/payment/confirm',
    '/profile',
];

if (isset($routes[$method][$uri])) {
    [$class, $action] = $routes[$method][$uri];
    if (in_array($uri, $protected)) {
        $session = new \Core\Session();
        if (!$session->isAuthenticated()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }
    (new $class())->$action();
} else {
    http_response_code(404);
    require __DIR__ . '/views/errors/404.php';
}
