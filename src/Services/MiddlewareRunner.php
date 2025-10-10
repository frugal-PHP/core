<?php

namespace Frugal\Core\Services;

use Psr\Http\Message\ServerRequestInterface;

class MiddlewareRunner
{
    private array $middlewares;

    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    public function __invoke(ServerRequestInterface $request) : ServerRequestInterface
    {
        $middlewareChain = array_reduce(
            array_reverse($this->middlewares),
            fn ($next, $middleware) => fn ($request) => $middleware($request, $next),
            fn (ServerRequestInterface $request) => $request
        );

        return $middlewareChain($request);
    }
}
