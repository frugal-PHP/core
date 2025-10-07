<?php

namespace Frugal\Core\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareInterface
{
    public function __invoke(ServerRequestInterface $request, ?callable $next);
}