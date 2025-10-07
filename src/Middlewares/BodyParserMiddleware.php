<?php

namespace Frugal\Core\Middlewares;

use Frugal\Core\Exceptions\InvalidPayloadException;
use Frugal\Core\Interfaces\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class BodyParserMiddleware implements MiddlewareInterface
{
    public function __invoke(ServerRequestInterface $request, ?callable $next)
    {
        $contentType = $request->getHeaderLine('Content-Type');
        $body = (string) $request->getBody();

        $parsed = null;

        if (str_starts_with($contentType, 'application/json')) {
            $parsed = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidPayloadException("Invalid json body");
            }
        } elseif (str_starts_with($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($body, $parsed);
        }

        if (is_array($parsed)) {
            $request = $request->withParsedBody($parsed);
        }

        return $next ? $next($request) : $request;
    }
}