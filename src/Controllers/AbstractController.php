<?php

namespace Frugal\Core\Controllers;

use React\Http\Message\Response;

abstract class AbstractController
{   
    protected array $payload;

    public function sendJsonResponse(
        int $statusCode,
        mixed $message
    ) {
        $headers = ['Content-Type' => 'application/json'];
        $response = new Response($statusCode, $headers);
        if($message !== null) {
            $response->getBody()->write(json_encode($message));
        }
        
        return $response;
    }
}