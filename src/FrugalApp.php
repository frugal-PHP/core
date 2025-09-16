<?php

namespace Frugal\Core;

use Frugal\Core\Commands\CommandInterpreter;
use Frugal\Core\Middlewares\BodyParserMiddleware;
use Frugal\Core\Services\Bootstrap;
use Frugal\Core\Services\LogService;
use Frugal\Core\Services\MiddlewareRunner;
use Frugal\Core\Services\ResponseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Socket\SocketServer;
use Wilaak\Http\RadixRouter;
use Throwable;

class FrugalApp
{
    public static function run(array $middlewares = []) : void
    {
        define('START_TS', microtime(true));
        define('MEMORY_ON_START', memory_get_usage(true));

        // Bootstrap
        Bootstrap::loadEnv();
        if(getenv('SERVER_HOST') === false || getenv('SERVER_PORT') === false) {
            echo "\n⚠️ --- Server need SERVER_HOST and SERVER_PORT in .env defined to start.\nAbort.\n\n";
            exit;
        }

        Bootstrap::autoloadPlugins();
        
        if($_SERVER['argc'] > 1) {
            CommandInterpreter::run();
            $memoryPeak = memory_get_peak_usage(true)/1024/1024;
            $startDelay = round(microtime(true) - START_TS,4);
            echo "🕒 Lancement en ".$startDelay."s\n";
            echo "🧠 Mémoire consommée : ".$memoryPeak." Mb\n\n";
            exit(0);
        }

        // Routing
        $router = new RadixRouter();
        $routes = require $ROOT_DIR."/config/routing.php";
        foreach($routes as $method => $data) {
            foreach($data as $uri => $handler) {
                $router->add($method, $uri, $handler);
            }
        }

        $controller = function (ServerRequestInterface $request, float $queryStart) use ($router) {
            try {
                $result = $router->lookup(
                    method: $request->getMethod(), 
                    path:$request->getUri()->getPath()
                );

                switch ($result['code']) {
                    case 200:
                       $route = $result['handler'];

                        if (is_array($route) && isset($route['handler'])) {
                            $class = $route['handler'];
                            return (new $class)(
                                $request,
                                $route['action'] ?? null,
                                $route['entityClassName'] ?? null,
                                $route['payloadClassName'] ?? null,
                                $result['params']['id'] ?? null
                            );
                        }

                        return (new $route)($request, ...$result['params']);
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
            }
            catch (Throwable $e) {
                return ResponseService::error(e: $e, req: $request, start: $queryStart );
            }

        };

        $server = new HttpServer(function($request) use ($controller, $middlewares) {
            $queryStart = microtime(true);
            $memoryStartUsage = memory_get_usage(true);
            $middlewareRunner = new MiddlewareRunner(
                array_merge([new BodyParserMiddleware()], $middlewares)
            );

            $promise = $controller($middlewareRunner($request), $queryStart)
                ->then(function(ResponseInterface $response) use ($request, $memoryStartUsage, $queryStart) {
                    LogService::logAccess(
                        request: $request,
                        queryStart: $queryStart,
                        memoryStartUsage: $memoryStartUsage
                    );

                    return $response;
                })
                ->catch(function(Throwable $e) use ($request, $memoryStartUsage, $queryStart) {
                    return ResponseService::error($e, $request, $queryStart);
                });

            return $promise;
        });

        $socket = new SocketServer(getenv('SERVER_HOST').":".getenv('SERVER_PORT'));
        $server->listen($socket);

        $memoryPeak = memory_get_peak_usage(true)/1024/1024;
        $startDelay = round(microtime(true) - START_TS,4);

        echo "\n✅ Serveur lancé sur http://".getenv('SERVER_HOST').":".getenv('SERVER_PORT')."\n";
        echo "🕒 Lancement en ".$startDelay."s\n";
        echo "🧠 Mémoire consommée : ".$memoryPeak." Mb\n\n";
        Loop::get()->run();
    }

    /**
     * Ai-generated
     * @param callable $generatorFn 
     * @return PromiseInterface 
     */
    public static function coroutine(callable $generatorFn): PromiseInterface
    {
        $deferred = new Deferred();

        try {
            $gen = $generatorFn();
        } catch (\Throwable $e) {
            $deferred->reject($e);
            return $deferred->promise();
        }

        $advance = function ($yielded = null) use (&$advance, $gen, $deferred) {
            try {
                $yielded = $gen->send($yielded);

                if ($yielded instanceof PromiseInterface) {
                    $yielded->then(
                        fn($value) => $advance($value),
                        fn($err)   => $deferred->reject($err)
                    );
                } elseif ($gen->valid()) {
                    throw new \RuntimeException("Generator yielded a non-promise value");
                } else {
                    $deferred->resolve($gen->getReturn());
                }
            } catch (\Throwable $e) {
                $deferred->reject($e);
            }
        };

        $advance();

        return $deferred->promise();
    }
}