<?php

namespace Frugal\Core\Router;

use Frugal\Core\Interfaces\RoutingInterface;
use Frugal\Core\Mappers\HTTPVerb;

class RoutingMap implements RoutingInterface
{
    protected array $routes;

    public function get(
        string $uri,
        string $handler
    ) : Route
    {
        return $this->add(HTTPVerb::GET, $uri, $handler);
    }

    public function post(
        string $uri, 
        string $handler
    ) : Route
    {
        return $this->add(HTTPVerb::POST, $uri, $handler);
    }

    public function put(
        string $uri,
        string $handler
    ) : Route
    {
        return $this->add(HTTPVerb::PUT, $uri, $handler);
    }

    public function patch(
        string $uri,
        string $handler
    ) : Route
    {
        return $this->add(HTTPVerb::PATCH, $uri, $handler);
    }

    public function options(
        string $uri,
        string $handler
    ) : Route
    {
        return $this->add(HTTPVerb::OPTIONS, $uri, $handler);
    }

    public function head(
        string $uri,
        string $handler
    ) : Route
    {
        return $this->add(HTTPVerb::HEAD, $uri, $handler);
    }

    public function delete(
        string $uri, 
        string $handler
    ): Route 
    { 
        return $this->add(HTTPVerb::DELETE, $uri, $handler);
    }

    protected function add(
        HTTPVerb $verb,
        string $uri,
        string $handler,
    ) : Route
    {
        $route = new Route($verb, $uri, $handler);
        $this->routes[] = $route;
        
        return $route;
    }

    public function getAll(): array
    {
        return $this->routes;
    }
}