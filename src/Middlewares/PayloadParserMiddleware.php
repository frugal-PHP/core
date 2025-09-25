<?php

namespace Frugal\Core\Middlewares;

use Frugal\Core\Helpers\RoutingHelper;
use Frugal\Core\Interfaces\PayloadInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class PayloadParserMiddleware
{
    public function __invoke(ServerRequestInterface $request, ?callable $next)
    {
        $payloadClassName = RoutingHelper::getRouteDetails($request, 'payloadClassName');
        if($payloadClassName) {
            if(!class_exists($payloadClassName) || !is_subclass_of($payloadClassName, PayloadInterface::class)) {
                throw new RuntimeException("Invalid payload class for route ".$request->getUri());
            }

            $request = $request->withAttribute('payload', $payloadClassName::fromRequest($request));
        }
        
        return $next ? $next($request) : $request;
    }
}