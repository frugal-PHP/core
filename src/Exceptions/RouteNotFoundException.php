<?php

namespace Frugal\Core\Exceptions;

use Exception;

class RouteNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct(message: "Route not found", code: 404, previous: null);
    }
}