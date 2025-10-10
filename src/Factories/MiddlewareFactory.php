<?php

namespace Frugal\Core\Factories;

use Frugal\Core\Services\MiddlewareRunner;

class MiddlewareFactory
{
    public static function with(array $middlewares): callable
    {
        return new MiddlewareRunner($middlewares);
    }
}
