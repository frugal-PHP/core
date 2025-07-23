<?php

namespace Frugal\Core\Controllers;

use React\Http\Message\Response;

abstract class AbstractController
{    
    public function sendResponse(
        int $statusCode,
        ?string $message
    ) {
        $headers = ['Content-Type' => 'application/json'];
        $response = new Response($statusCode, $headers);
        if($message !== null) {
            $response->getBody()->write($message);
        }
        
        return $response;
    }
}