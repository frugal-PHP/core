<?php

namespace Frugal\Core\Services;

use React\Http\Message\Response;

class ResponseService
{
    public static function sendJsonResponse(
        int $statusCode,
        mixed $message
    ): Response {
        $headers = ['Content-Type' => 'application/json'];
        $response = new Response($statusCode, $headers);
        
        if($message !== null) {
            $json = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $response->getBody()->write($json);
        }
        
        return $response;
    }
}