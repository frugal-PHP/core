<?php

namespace Frugal\Core\Payloads;

use Frugal\Core\Interfaces\PayloadInterface;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class AbstractPayload implements PayloadInterface
{
    public static function checkMandatory($payload): void
    { 
         foreach(static::getMandatoryFields() as $field) {
            if(!isset($requestPayload[$field])) {
                throw new InvalidArgumentException(code: 400, message: "Field $field is mandatory");
            }
        }
    }

    abstract protected static function getMandatoryFields() : array;
    abstract static public function fromRequest(ServerRequestInterface $request): static;
}