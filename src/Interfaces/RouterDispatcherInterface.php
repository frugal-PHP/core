<?php

namespace Frugal\Core\Interfaces;

use Frugal\Core\Router\Route;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;

interface RouterDispatcherInterface
{
    public function dispatch(ServerRequestInterface $request) : PromiseInterface;
    public function registerRoute(Route $route);
}
