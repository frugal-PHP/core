<?php

namespace Frugal\Core\Router;

use Frugal\Core\Interfaces\MiddlewareInterface;
use Frugal\Core\Mappers\HTTPVerb;

class Route
{
    protected array $additionalParameters = [];
    protected array $middlewares = [];
    protected string $uri;
    protected string $handler;

    public function __construct(
        protected HTTPVerb $verb,
        string $uri,
        string $handler
    )
    {
        $this->setUri($uri)->setHandler($handler);
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }

    public function getVerb(): HTTPVerb
    {
        return $this->verb;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getAdditionalParameters(): array
    {
        return $this->additionalParameters;
    }

    protected function setUri(string $uri) : self
    {
        // Additionnal checks to be sure to be compliant with HTTP Protocol.
        $this->uri = $uri;

        return $this;
    }

    protected function setHandler(string $handler) : self
    {
        $this->handler = $handler;

        return $this;
    }

    public function withAdditionalParameters(array $routeParameters) : self
    {
        $this->additionalParameters = $routeParameters;

        return $this;
    }

    public function withMiddlewares(array $middlewares): self
    {
        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }

        return $this;
    }

    protected function addMiddleware(MiddlewareInterface $middleware) : self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }
}
