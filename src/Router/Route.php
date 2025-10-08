<?php

namespace Frugal\Core\Router;

use Frugal\Core\Mappers\HTTPVerb;

class Route
{
    public function __construct(
        public string $handler,
        public string $uri,
        public HTTPVerb $verb
    ) {}
}