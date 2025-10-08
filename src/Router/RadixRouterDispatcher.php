<?php

namespace Frugal\Core\Router;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;
use RuntimeException;
use Wilaak\Http\RadixRouter;

use function React\Promise\reject;

class RadixRouterDispatcher extends AbstractDispatcher 
{
    protected RadixRouter $router;

    public function __construct() {
        $this->router = new RadixRouter();
        $this->registerRoutes();
    }

    public function registerRoutes()
    {
        foreach($this->getAllRoutes() as $routeDetails) {
            $this->router->add($routeDetails->verb->value, $routeDetails->uri, $routeDetails);
            echo "  ➤ ".$routeDetails->verb->value." ".$routeDetails->uri."\n";
        }
    }

    public function dispatch(ServerRequestInterface $request) : PromiseInterface
    { 
        $result = $this->router->lookup(
                method: $request->getMethod(), 
                path:$request->getUri()->getPath()
            );

        switch ($result['code']) {
            case 200:
                $request = $request->withAttribute('route_details', $result['handler']);

                return (new $result['handler']->handler)($request, ...$result['params']);
            case 404:
                // No matching route found
                return \React\Promise\resolve(
                    new Response(status: Response::STATUS_NOT_FOUND, reason: "Route not found")
                );
            case 405:
                // Method not allowed for this route
                return \React\Promise\resolve(
                    new Response(Response::STATUS_METHOD_NOT_ALLOWED, ['Allow' => implode(', ', $result['allowed_methods'])])
                );
        }

        return reject(new RuntimeException("Dispatcher failed"));
    }
}