<?php

namespace Frugal\Core\Helpers;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

class JSONPayloadHelper
{
    public static function decodePayload(ServerRequestInterface $request) : array
    {
        $payload = json_decode($request->getBody(), true);
        if (!is_array($payload)) {
            throw new InvalidArgumentException(message: "Invalid JSON payload");
        }

        return $payload;
    }
}

