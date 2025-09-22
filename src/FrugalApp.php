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
use React\Socket\ConnectionInterface;
use React\Socket\SecureServer;
use React\Socket\SocketServer;
use Wilaak\Http\RadixRouter;
use Throwable;

class FrugalApp
{
    private static array $connections;

    public static function run(array $middlewares = [], ?array $sslContext = null) : void
    {
        define('START_TS', microtime(true));
        define('MEMORY_ON_START', memory_get_usage(true));

        // Bootstrap
        Bootstrap::loadEnv();
        if(getenv('SERVER_HOST') === false || getenv('SERVER_PORT') === false) {
            echo "\n⚠️ --- Server need SERVER_HOST and SERVER_PORT in .env defined to start.\nAbort.\n\n";
            exit;
        }

        if(getenv('ROOT_DIR') === false) {
            echo "\n⚠️ --- ROOT_DIR in .env needs to be defined to start.\nAbort.\n\n";
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
        $routes = require ROOT_DIR."/config/routing.php";
        echo "⚙️ Chargement du routing \n";
        foreach($routes as $method => $data) {
            foreach($data as $uri => $handler) {
                $router->add($method, $uri, $handler);
                echo "  Route ajoutée : ($method) $uri\n";
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

            // Récupérer l'ID de connexion à partir des paramètres du serveur
            $remoteAddr = $request->getServerParams()['REMOTE_ADDR'] ?? '';
            $remotePort = $request->getServerParams()['REMOTE_PORT'] ?? '';
            $connectionId = "tls://".$remoteAddr . ':' . $remotePort;
            
            // Ajouter les informations de connexion à la requête
            $connection = self::$connections[$connectionId] ?? null;
            $request = $request->withAttribute('connection', $connection);

            $middlewareRunner = new MiddlewareRunner(
                array_merge([new BodyParserMiddleware()], $middlewares)
            );

            try {
                $output = $controller($middlewareRunner($request), $queryStart);
                if($output instanceof Response) {
                    return $output;
                }

                return $output->then(function(ResponseInterface $response) use ($request, $memoryStartUsage, $queryStart, $controller) 
                {
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
            } catch (Throwable $e) {
                return ResponseService::error($e, $request, $queryStart);
            }
        });

        $socket = new SocketServer(getenv('SERVER_HOST').":".getenv('SERVER_PORT'));
        if($sslContext !== null) {
            $socket = new SecureServer(context: $sslContext, tcp: $socket);
            echo "\n✅ SSL activé\n";
        } 
        
        $server->listen($socket);

        $socket->on('connection', function (ConnectionInterface $conn) {
            $remoteAddress = $conn->getRemoteAddress();
            self::$connections[$remoteAddress] = $conn;

            // Nettoyez lorsque la connexion se ferme
            $conn->on('close', function () use ($remoteAddress) {
                unset(self::$connections[$remoteAddress]);
            });
        });
        
        $memoryPeak = memory_get_peak_usage(true)/1024/1024;
        $startDelay = round(microtime(true) - START_TS,4);

        echo "\n✅ Serveur lancé sur ".getenv('SERVER_HOST').":".getenv('SERVER_PORT')."\n";
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

        $onFulfilled = function($value) use (&$next, $gen) {
            try {
                var_dump('kkkk');
                var_dump($value);
                $next($gen->send($value));
            } catch (\Throwable $e) {
                $next($e);
            }
        };

        $onRejected = function($reason) use (&$next, $gen) {
            try {
                var_dump('fffff');
                $next($gen->throw($reason));
            } catch (\Throwable $e) {
                $next($e);
            }
        };

        $next = function($value) use ($deferred, &$onFulfilled, &$onRejected, $gen) {
            var_dump('neseee');
            try {
                if ($value instanceof \Generator) {
                    // Si c'est un autre générateur, on le transforme en promise
                    $value = self::coroutine(function() use ($value) { yield from $value; });
                }

                if ($value instanceof PromiseInterface) {
                    var_dump('promise');
                    $value->then($onFulfilled, $onRejected);
                } else if ($gen->valid()) {
                    var_dump('valsuivante');
                    // Continue avec la valeur suivante
                    $onFulfilled($value);
                } else {
                    var_dump('gen tereminé');
                    var_dump($value);
                    // Générateur terminé, on résout avec la valeur de retour
                    $deferred->resolve($value);
                }
            } catch (\Throwable $e) {
                $deferred->reject($e);
            }
        };

        try {
            $next($gen->current());
        } catch (\Throwable $e) {
            $deferred->reject($e);
        }

        return $deferred->promise();
    }
}