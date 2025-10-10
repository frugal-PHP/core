<?php

namespace Frugal\Core\Services;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use RuntimeException;
use Throwable;

class LogService
{
    public static function logMemory() : void
    {
        $memReal = memory_get_usage(true)/1024/1024;
        echo "🧠 Current memory consumption : {$memReal} Mb".PHP_EOL;
    }

    public static function logRequest(ServerRequestInterface $request) : void
    {
        echo "✅ Nouvelle requete : [{$request->getMethod()}] {$request->getUri()->getPath()}\n";
    }

    public static function logInfo(string $message) : void
    {
        echo "ℹ️ $message".PHP_EOL;
    }

    public static function logError(string $message) : void
    {
        echo "⛔ $message".PHP_EOL;
    }

    public static function logRouteDetails(ServerRequestInterface $request): void
    {
        var_dump($request->getAttribute('route_details'));
    }

    public static function logStatusCode(ResponseInterface $responseInterface)
    {
        $statusCode = $responseInterface->getStatusCode();

        $icon = match (true) {
            $statusCode >= 100 && $statusCode < 200 => '🔵',
            $statusCode >= 200 && $statusCode < 300 => '🟢',
            $statusCode >= 300 && $statusCode < 400 => '🟣',
            $statusCode >= 400 && $statusCode < 500 => '🟠',
            $statusCode >= 500 => '🔴',
            default => '⚫️',
        };

        echo "{$icon} HTTP {$statusCode}" . PHP_EOL;
    }

    public static function logException(Throwable $e): void
    {
        echo "Erreur : {$e->getMessage()}\n";
        echo "Line : {$e->getLine()}\n";
        echo "File : {$e->getFile()}\n";

        if ($e instanceof RuntimeException) {
            echo "Stack : {$e->getTraceAsString()}\n";
        }
    }
}
