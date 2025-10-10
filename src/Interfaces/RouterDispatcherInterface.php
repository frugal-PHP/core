<?php

namespace Frugal\Core\Interfaces;

use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;

interface RouterDispatcherInterface
{
    public function dispatch(ServerRequestInterface $request) : PromiseInterface;
    public function registerRoutes();
}
