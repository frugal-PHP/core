<?php

namespace Frugal\Core\Payloads;

use Frugal\Core\Interfaces\PayloadInterface;
use InvalidArgumentException;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class AbstractPayload implements PayloadInterface, JsonSerializable
{
    abstract protected static function getMandatoryFields() : array;
    abstract public function getRequestBodyArray() : array;
    abstract static protected function makeRequestChecks(ServerRequestInterface $request, array $payload) : static;

    public static function checkMandatory(array $payload): void
    { 
         foreach(static::getMandatoryFields() as $field) {
            if(!isset($payload[$field])) {
                throw new InvalidArgumentException(code: 400, message: "Field $field is mandatory");
            }
        }

    }

    static public function fromRequest(ServerRequestInterface $request) : self
    {
        $payload = $request->getParsedBody();
        if (!is_array($payload)) {
            throw new InvalidArgumentException('Invalid payload: expected JSON object / form array');
        }

        static::checkMandatory($payload);

        return static::makeRequestChecks($request, $payload);
    }

    public function jsonSerialize() : mixed 
    {
        return array_filter($this->getRequestBodyArray(), fn($value) => $value !== null);
    }
}