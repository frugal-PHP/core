<?php

namespace Frugal\Core\Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Wilaak\Http\RadixRouter;

class RoutingMiddleware
{
    public function __construct(private RadixRouter $router) {}
    
    public function __invoke(ServerRequestInterface $request, ?callable $next)
    {
        $result = $this->router->lookup(
                method: $request->getMethod(), 
                path:$request->getUri()->getPath()
            );

        $request = $request->withAttribute('route_details', $result);

        return $next($request);
    }
}