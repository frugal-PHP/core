<?php

namespace Frugal\Core\Exceptions;

class RouteNotFoundException extends CustomException
{
    public function __construct()
    {
        parent::__construct(message: "Route not found", code: 404, previous: null);
    }
}