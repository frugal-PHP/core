<?php

namespace Frugal\Core\Interfaces;

use Frugal\Core\Router\Route;

interface RoutingInterface
{
    public function get(string $uri, string $handler): Route;
    public function post(string $uri, string $handler): Route;
    public function put(string $uri, string $handler): Route;
    public function patch(string $uri, string $handler): Route;
    public function delete(string $uri, string $handler): Route;
    public function options(string $uri, string $handler): Route;
    public function head(string $uri, string $handler): Route;

    /**
     * @return Route[] 
     */
    public function getAll() : array;
}