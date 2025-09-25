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
            $response->getBody()->write(json_encode($message));
        }
        
        return $response;
    }
}