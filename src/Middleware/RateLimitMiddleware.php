<?php
namespace Middleware;

use Core\RateLimiter;
use Core\Request;
use Core\Response;

class RateLimitMiddleware
{

    public function __construct(private string $action)
    {
    }

    public function handle(): void
    {
        $identifier = Request::ip();
        if (!RateLimiter::check($identifier, $this->action)) {
            Response::error('Too many requests. Please try again later.', 429);
        }
    }
}
