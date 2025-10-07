<?php

namespace Frugal\Core\Managers;

use Frugal\Core\Exceptions\BusinessException;
use Frugal\Core\Interfaces\ExceptionManagerInterface;
use Frugal\Core\Services\ResponseService;
use FrugalPhpPlugin\Orm\Exceptions\EntityNotFoundException;
use React\Http\Message\Response;
use Throwable;

class ExceptionManager implements ExceptionManagerInterface
{
    public function __invoke(Throwable $e)
    {
        if($e instanceof BusinessException) {
            return ResponseService::sendJsonResponse(
                statusCode: $e->getCode(), 
                message: $e->getMessage()
            );
        }
        
        if($e instanceof EntityNotFoundException) {
            return ResponseService::sendJsonResponse(
                statusCode: Response::STATUS_NOT_FOUND, 
                message: "Object not found"
            );
        }

        var_dump($e->getMessage(), $e->getLine(), $e->getTraceAsString());
        
        return ResponseService::sendJsonResponse(
            statusCode: Response::STATUS_INTERNAL_SERVER_ERROR,
            message: "Internal server error. Please retry later."
        );
    }
}