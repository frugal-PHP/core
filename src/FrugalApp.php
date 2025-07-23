<?php

namespace Frugal\Core;

use Frugal\Core\Commands\CommandInterpreter;
use Frugal\Core\Exceptions\RouteNotFoundException;
use Frugal\Core\Services\Bootstrap;
use Frugal\Core\Services\LogService;
use Frugal\Core\Services\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
use Throwable;

use function React\Promise\resolve;

class FrugalApp
{
    public static function run() : void
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
            exit(0);
        }

        Bootstrap::compileRoute(
            staticFile: ROOT_DIR."/config/routing/static.php", 
            dynamicFile: ROOT_DIR."/config/routing/dynamic.php"
        );

        $router = new Router(Bootstrap::$compiledRoutes);
        $loop = Loop::get();

        $server = new HttpServer(function (ServerRequestInterface $request) use ($router) {
            $startRequestTS = microtime(true);
            try {
                $promise = $router->dispatch($request);
            } catch(\Throwable $e) {
                return resolve(FrugalApp::errorResponse($e, 500, $request, $startRequestTS));
            }

            return $promise->then(
                fn(ResponseInterface $res) => FrugalApp::logAndReturn($res, $request, $startRequestTS),
                fn(\Throwable $e)          => FrugalApp::errorResponse($e, $e instanceof RouteNotFoundException ? 404 : 500, $request, $startRequestTS)
            );
        });

        $socket = new SocketServer(getenv('SERVER_HOST').":".getenv('SERVER_PORT'));
        $server->listen($socket);
        $memoryPeak = memory_get_peak_usage(true)/1024/1024;
        $startDelay = round(microtime(true) - START_TS,4);

        echo "\n✅ Serveur lancé sur http://".getenv('SERVER_HOST').":".getenv('SERVER_PORT')."\n";
        echo "🕒 Lancement en ".$startDelay."s\n";
        echo "🧠 Mémoire consommée : ".$memoryPeak." Mb\n\n";
        $loop->run();
    }

    private static function logAndReturn(ResponseInterface $res, ServerRequestInterface $req, float $start): ResponseInterface
    {
        LogService::logAccess($req, $start, $res->getStatusCode());

        return $res;
    }

    private static function errorResponse(\Throwable $e, int $status, ServerRequestInterface $req, float $start): Response
    {
        LogService::logError($req, $start, $e, $status);

        return new Response(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE)
        );
    }
}