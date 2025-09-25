<?php

namespace Frugal\Core;

use Frugal\Core\Commands\CommandInterpreter;
use Frugal\Core\Middlewares\BodyParserMiddleware;
use Frugal\Core\Middlewares\PayloadParserMiddleware;
use Frugal\Core\Middlewares\RoutingMiddleware;
use Frugal\Core\Services\Bootstrap;
use Frugal\Core\Services\LogService;
use Frugal\Core\Services\MiddlewareRunner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\ConnectionInterface;
use React\Socket\SecureServer;
use React\Socket\SocketServer;
use RuntimeException;
use Wilaak\Http\RadixRouter;
use Throwable;

class FrugalApp
{
    private static array $connections;

    public static function run(
        object $exceptionManager,
        array $middlewares = [],
        ?array $sslContext = null
    ) : void
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

        $middlewares = [new RoutingMiddleware($router), new BodyParserMiddleware(), new PayloadParserMiddleware(), ...$middlewares];

        $controller = function (ServerRequestInterface $request, float $queryStart) use ($router) {
            $result = $request->getAttribute('route_details');
            switch ($result['code']) {
                case 200:
                    $route = $result['handler'];

                    if (is_array($route)) {
                        if(!array_key_exists('handler', $route)) {
                            throw new RuntimeException("Route parameters error. No handler for route ".$request->getUri());
                        }
                        $route = $route['handler'];
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
        };

        $server = new HttpServer(function($request) use ($controller, $middlewares, $exceptionManager) {
            $queryStart = microtime(true);
            $memoryStartUsage = memory_get_usage(true);

            // Récupérer l'ID de connexion à partir des paramètres du serveur
            $remoteAddr = $request->getServerParams()['REMOTE_ADDR'] ?? '';
            $remotePort = $request->getServerParams()['REMOTE_PORT'] ?? '';
            $connectionId = "tls://".$remoteAddr . ':' . $remotePort;
            
            // Ajouter les informations de connexion à la requête
            $connection = self::$connections[$connectionId] ?? null;
            $request = $request->withAttribute('connection', $connection);

            $middlewareRunner = new MiddlewareRunner($middlewares);

            try {
                return $controller($middlewareRunner($request), $queryStart)
                    ->then(function(ResponseInterface $response) use ($request, $memoryStartUsage, $queryStart) 
                    {
                        LogService::logAccess(
                            request: $request,
                            queryStart: $queryStart,
                            memoryStartUsage: $memoryStartUsage
                        );

                        return $response;
                    })->otherwise(function (Throwable $e) use ($exceptionManager) {
                        return $exceptionManager($e);
                    });
            } catch (Throwable $e) {
                return $exceptionManager($e);
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
}