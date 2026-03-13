<?php
namespace Middleware;

use Core\Session;
use Core\Response;

class AuthMiddleware
{
    public function handle(): void
    {
        $session = new Session();
        if (!$session->isAuthenticated()) {
            Response::redirect('login');
        }
    }
}
