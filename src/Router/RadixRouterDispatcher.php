<?php

namespace Frugal\Core\Router;

use Frugal\Core\Interfaces\RouterDispatcherInterface;
use Frugal\Core\Interfaces\RoutingInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;
use RuntimeException;
use Wilaak\Http\RadixRouter;

use function React\Promise\reject;

class RadixRouterDispatcher implements RouterDispatcherInterface
{
    protected RadixRouter $router;

    public function __construct(
        public RoutingInterface $routingMap
    ) {
        $this->router = new RadixRouter();
    }

    public function registerRoutes(RoutingInterface $routing)
    {
        foreach($routing->getAll() as $route) {
            $this->router->add($route->getVerb()->value, $route->getUri(), ['handler' => $route->getHandler(), ...$route->getAdditionalParameters()]);
            echo "  ➤ ".$route->getVerb()->value." ".$route->getUri()."\n";
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
                $route = $result['handler'];

                if (is_array($route)) {
                    if(!array_key_exists('handler', $route)) {
                        throw new RuntimeException("Route parameters error. No handler for route ".$request->getUri());
                    }
                    $handler = $route['handler'];
                }

                // A déplacer
                $request = $request->withAttribute('route_details', $route);

                return (new $handler)($request, ...$result['params']);
            case 404:
                // No matching route found
                return \React\Promise\resolve(
                    new Response(Response::STATUS_NOT_FOUND)
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