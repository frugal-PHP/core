<?php

namespace Frugal\Core\Exceptions;

use React\Http\Message\Response;

class ForbiddenAccessException extends BusinessException
{
    public function __construct()
    {
        parent::__construct(
            message: "Access forbidden",
            code: Response::STATUS_FORBIDDEN
        );
    }
}
