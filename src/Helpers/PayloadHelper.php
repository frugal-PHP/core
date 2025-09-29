<?php

namespace Frugal\Core\Helpers;

use Frugal\Core\Interfaces\PayloadInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class PayloadHelper
{
    public static function getPayload(ServerRequestInterface $request) : PayloadInterface
    {
        $payload = $request->getAttribute('payload');
        if($payload === null) {
            throw new RuntimeException("Payload requested but no payload is present in the request attributes");
        }

        return $payload;
    }
}