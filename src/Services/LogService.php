<?php

namespace Frugal\Core\Services;

use Frugal\Core\Exceptions\CustomException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

class LogService
{
    public static function logAccess(ServerRequestInterface $request, float $queryStart, float $memoryStartUsage): void
    {
        $memReal = (memory_get_usage(true) - $memoryStartUsage)/1024/1024;
        $memPeak = memory_get_peak_usage(true)/1024/1024;
        $delay = (microtime(true) - $queryStart)*1000;
        echo "✅ [{$request->getMethod()}] {$request->getUri()->getPath()}\n";
        echo "Real: {$memReal} Mb\n";
        echo "🧠 Peak : {$memPeak} Mb\n";
        echo "🕒 {$delay}ms\n\n";
    }

    public static function logError(ServerRequestInterface $req, float $start, Throwable $e, int $status): void
    {
        $delay = round(microtime(true) - $start, 4) * 1000;
        $mem   = memory_get_peak_usage(true)/1024/1024;
        echo "❌ [{$req->getMethod()}] {$req->getUri()->getPath()} ($status)\n";
        echo "🧠 Peak : {$mem} Mb\n";
        echo "🕒 {$delay} ms\n\n";
        echo "Erreur : {$e->getMessage()}\n";
        echo "Line : {$e->getLine()}\n";
        echo "File : {$e->getFile()}\n";

        if($e instanceof RuntimeException) {
            echo "Stack : {$e->getTraceAsString()}\n";
        }
    }
}