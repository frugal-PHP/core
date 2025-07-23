<?php

namespace Frugal\Core\Services;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class LogService
{
    public static function logAccess(ServerRequestInterface $req, float $start, int $status): void
    {
        $delay = round(microtime(true) - $start, 4);
        $mem   = memory_get_peak_usage(true)/1024/1024;
        echo "✅ [{$req->getMethod()}] {$req->getUri()->getPath()} ($status)\n";
        echo "🧠 Peak : {$mem} Mb\n";
        echo "🕒 {$delay}s\n\n";
    }

    public static function logError(ServerRequestInterface $req, float $start, Throwable $e, int $status): void
    {
        $delay = round(microtime(true) - $start, 4);
        echo "❌ [{$req->getMethod()}] {$req->getUri()->getPath()} ($status)\n";
        echo "🕒 {$delay}s\n\n";
        echo "Erreur : {$e->getMessage()}\n";
        echo "Stack : {$e->getTraceAsString()}\n";
    }
}