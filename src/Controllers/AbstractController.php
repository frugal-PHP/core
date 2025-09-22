<?php

namespace Frugal\Core\Controllers;

use React\Http\Message\Response;
use React\Promise\PromiseInterface;

abstract class AbstractController
{   
    public function sendJsonResponse(
        int $statusCode,
        mixed $message
    ): PromiseInterface {
        $headers = ['Content-Type' => 'application/json'];
        $response = new Response($statusCode, $headers);
        
        if($message !== null) {
            $response->getBody()->write(json_encode($message));
        }
        
        return \React\Promise\resolve($response);
    }
}