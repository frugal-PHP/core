<?php

namespace Frugal\Core\Exceptions;

use React\Http\Message\Response;

class InvalidPayloadException extends BusinessException
{
    public function __construct(string $message)
    {
        parent::__construct(message: $message, code: Response::STATUS_BAD_REQUEST);
    }
}