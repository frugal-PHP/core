<?php

use Frugal\Core\Commands\CommandInterpreter;
use Frugal\Core\Services\Bootstrap;
use Psr\Http\Message\ResponseInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;

define('START_TS', microtime(true));
define('MEMORY_ON_START', memory_get_usage(true));
define('ROOT_DIR', __DIR__);

require(__DIR__.'/vendor/autoload.php');

// Mode commande
if($_SERVER['argc'] > 1) {
    return CommandInterpreter::run();
}

// Bootstrap
Bootstrap::loadEnv();
$router = Bootstrap::compileRoute();

$loop = React\EventLoop\Loop::get();

$server = new HttpServer(function (Psr\Http\Message\ServerRequestInterface $request) use ($router) {
    return $router->dispatch($request)
        ->then(
            onFulfilled: 
                function(ResponseInterface $response) use ($request) {
                    $method = $request->getMethod();
                    $uri = $request->getUri()->getPath();
                    $memoryPeak = memory_get_peak_usage(true)/1024;

                    echo "URL : [$method] ".$uri."\n";
                    echo "memoire en peak : ".$memoryPeak." Kb\n";

                    return $response;
                },
            onRejected:
                function(Throwable $e) {
                    var_dump($e->getMessage()); die;
                    return new Response(Response::STATUS_NOT_FOUND, [], '404 Not Found');
                }
            );
});

$socket = new React\Socket\SocketServer(getenv('SERVER_HOST').":".getenv('SERVER_PORT'));
$server->listen($socket);

echo "Serveur lancé sur http://".getenv('SERVER_HOST').":".getenv('SERVER_PORT')."\n";
echo "Lancement en ".(microtime(true) - START_TS)."\n";
echo "Peak :".memory_get_peak_usage(true)."\n\n";
$loop->run();