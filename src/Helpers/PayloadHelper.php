<?php

namespace Frugal\Core\Helpers;

use Frugal\Core\Interfaces\PayloadInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class PayloadHelper
{
    public static function getPayload(ServerRequestInterface $request) : PayloadInterface
    {
        $payload = RoutingHelper::getRouteDetails($request, 'payloadClassName');
        if($payload === null) {
            throw new RuntimeException("Payload requested but no payload is present in the route details");
        }

        return $payload::fromRequest($request);
    }
}