<?php

namespace Frugal\Core;

use Frugal\Core\Commands\CommandInterpreter;
use Frugal\Core\Helpers\BenchmarkHelper;
use Frugal\Core\Interfaces\ExceptionManagerInterface;
use Frugal\Core\Interfaces\MiddlewareInterface;
use Frugal\Core\Interfaces\RouterDispatcherInterface;
use Frugal\Core\Middlewares\BodyParserMiddleware;
use Frugal\Core\Middlewares\PayloadParserMiddleware;
use Frugal\Core\Services\Bootstrap;
use Frugal\Core\Services\FrugalContainer;
use Frugal\Core\Services\MiddlewareRunner;
use Frugal\Core\SSL\SslContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Socket\ConnectionInterface;
use React\Socket\SecureServer;
use React\Socket\SocketServer;
use Throwable;

class FrugalApp
{
    private static array $connections;
    private static array $globalMiddlewares;

    public static function run(
        ExceptionManagerInterface $exceptionManager,
        RouterDispatcherInterface $router,
        ?SslContext $sslContext = null
    ) : void
    {
        define('START_TS', microtime(true));

        // Bootstrap
        self::initializeEnv();
        self::maybeRunCli();

        FrugalContainer::getInstance()->set('router', fn() => $router);

        $middlewareRunner = new MiddlewareRunner([
            new BodyParserMiddleware(), 
            new PayloadParserMiddleware(), 
            ...self::$globalMiddlewares
        ]);

        $server = self::setupHttpServer($router, $middlewareRunner, $exceptionManager, $sslContext);
        self::setUpServerInterface($server, $sslContext);
        self::displayMetrics();
        
        Loop::get()->run();
    }

    private static function initializeEnv(): void 
    {
        Bootstrap::loadEnv();
        if(Bootstrap::env('SERVER_HOST') === false || Bootstrap::env('SERVER_PORT') === false) {
            echo "\n⚠️ --- Server need SERVER_HOST and SERVER_PORT in .env defined to start.\nAbort.\n\n";
            exit;
        }

        if(Bootstrap::env('ROOT_DIR') === false) {
            echo "\n⚠️ --- ROOT_DIR in .env needs to be defined to start.\nAbort.\n\n";
            exit;
        }

        Bootstrap::autoloadPlugins();
    }

    private static function maybeRunCli(): void 
    {
        if($_SERVER['argc'] > 1) {
            CommandInterpreter::run();
            self::displayMetrics();
            exit(0);
        }
    }

    private static function setupHttpServer(
        RouterDispatcherInterface $router,
        MiddlewareRunner $middlewareRunner,
        ExceptionManagerInterface $exceptionManager,
        ?array $sslContext
    ): HttpServer 
    {
        return new HttpServer(function($request) use ($router, $middlewareRunner, $exceptionManager, $sslContext) {
            $benchmark = new BenchmarkHelper();
            try {
                if($sslContext !== null) {
                    $request = self::handleConnectionTracking($request);
                }
                $request = $middlewareRunner($request);

                return $router->dispatch($request)
                    ->then(function(ResponseInterface $response) use ($benchmark) {
                        $benchmark->log("Temps traitement requete");
                        return $response;
                    })
                    ->catch(fn(Throwable $e) => $exceptionManager($e));
            } catch (Throwable $e) {
                return $exceptionManager($e);
            }
        });
    }

    private static function handleConnectionTracking(ServerRequestInterface $request): ServerRequestInterface 
    {
        // Récupérer l'ID de connexion à partir des paramètres du serveur
        $remoteAddr = $request->getServerParams()['REMOTE_ADDR'] ?? '';
        $remotePort = $request->getServerParams()['REMOTE_PORT'] ?? '';
        $connectionId = "tls://".$remoteAddr . ':' . $remotePort;
        
        // Ajouter les informations de connexion à la requête
        $connection = self::$connections[$connectionId] ?? null;
        
        return $request->withAttribute('connection', $connection);
    }

    private static function setUpServerInterface(HttpServer $server, ?SslContext $sslContext = null) : void
    {
        $socket = new SocketServer(Bootstrap::env('SERVER_HOST').":".Bootstrap::env('SERVER_PORT'));
        if($sslContext !== null) {
            $socket = new SecureServer(context: $sslContext->toArray(), tcp: $socket);

            $socket->on('connection', function (ConnectionInterface $conn) {
                $remoteAddress = $conn->getRemoteAddress();
                self::$connections[$remoteAddress] = $conn;

                // Nettoyez lorsque la connexion se ferme
                $conn->on('close', function () use ($remoteAddress) {
                    unset(self::$connections[$remoteAddress]);
                });
            });

            echo "\n✅ SSL activé\n";
        }

        $server->listen($socket);
    }

    private static function displayMetrics() : void
    {
        $memoryPeak = memory_get_peak_usage(true)/1024/1024;
        $startDelay = round(microtime(true) - START_TS,4);

        echo "\n✅ Serveur lancé sur ".getenv('SERVER_HOST').":".getenv('SERVER_PORT')."\n";
        echo "🕒 Lancement en ".$startDelay."s\n";
        echo "🧠 Mémoire consommée : ".$memoryPeak." Mb\n\n";
    }

    public static function addGlobalMiddleware(MiddlewareInterface $middleware)
    {
        self::$globalMiddlewares[] = $middleware;
    }
}