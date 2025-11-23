<?php

namespace Frugal\Core\Router;

use Frugal\Core\Interfaces\RouterDispatcherInterface;
use Frugal\Core\Services\FrugalContainer;
use Frugal\Core\Services\LogService;
use Frugal\Core\Services\MiddlewareRunner;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;
use RuntimeException;
use Throwable;
use Wilaak\Http\RadixRouter;

use function React\Promise\reject;

class RadixRouterDispatcher implements RouterDispatcherInterface
{
    protected RadixRouter $router;

    public function __construct()
    {
        $this->router = new RadixRouter();
    }

    public function registerRoute(Route $route)
    {
        $this->router->add($route->verb->value, $route->uri, $route);
        echo "  ➤ ".$route->verb->value." ".$route->uri."\n";
    }

    public function dispatch(ServerRequestInterface $request) : PromiseInterface
    {
        $result = $this->router->lookup(
            method: $request->getMethod(),
            path:$request->getUri()->getPath()
        );

        try {
            switch ($result['code']) {
                case 200:
                    // Execute route middlewares if any
                    if (!empty($result['handler']->middlewares)) {
                        $middlewareRunner = new MiddlewareRunner($result['handler']->middlewares);
                        $request = $middlewareRunner($request);
                    }
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
        } catch (Throwable $e) {
            $exceptionManager = FrugalContainer::getInstance()->get('exceptionManager');
            LogService::logRouteDetails($request);
            LogService::logError($e->getMessage());
            LogService::logMemory();

            echo PHP_EOL;

            return $exceptionManager($e);
        }
    }
}
