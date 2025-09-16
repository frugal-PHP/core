<?php

namespace Frugal\Core\Services;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class ResponseService
{
    public static function error(\Throwable $e, ServerRequestInterface $req, float $start): Response
    {
        LogService::logError($req, $start, $e, $e->getCode());
        $code = $e->getCode() === 0 ? 500 : $e->getCode();

        if($code === 500) {
            return new Response(
                status: Response::STATUS_INTERNAL_SERVER_ERROR,
                reason: 'Unexpected server error. Please try later'
            );
        }

        return new Response(
            $code,
            ['Content-Type' => 'application/json'],
            json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE)
        );
    }
}