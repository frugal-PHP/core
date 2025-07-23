<?php

namespace Frugal\Core\Services;

use Exception;
use Frugal\Core\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;

use function React\Promise\reject;

class Router
{
    private array $routes;

    public function __construct(array $compiledRoutes)
    {
        $this->routes = $compiledRoutes;
    }

    public function dispatch(ServerRequestInterface $request) : PromiseInterface
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();

        // Cherchons si ça correspond à une route statique = match parfait.
        if(isset($this->routes['static'][$method][$uri])) {
            return $this->call(
                request: $request,
                callbackString: $this->routes['static'][$method][$uri]
            );
        }

        // C'est peut être une route dynamique
        foreach ($this->routes['dynamic'][$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return $this->call(
                    callbackString: $route["handler"], 
                    params: $params,
                    request: $request
                );
            }
        }

        throw new RouteNotFoundException;
    }

    private function call(
        ServerRequestInterface $request,
        string $callbackString, 
        array $params = []
    ) : PromiseInterface
    {
        [$className, $classMethod] = strpos($callbackString, '@')
            ? explode('@', $callbackString)
            : [$callbackString, '__invoke'];

        static $instances = [];
        $controller = $instances[$className] ??= new $className;

        return $controller->$classMethod($request, ...$params);
    }
}