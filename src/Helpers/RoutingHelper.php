<?php

namespace Frugal\Core\Helpers;

use Psr\Http\Message\ServerRequestInterface;

class RoutingHelper
{
    public static function getRouteDetails(ServerRequestInterface $request, string $key) : mixed
    {
        return $request->getAttribute('route_details')->$key ?? null;
    }
}
