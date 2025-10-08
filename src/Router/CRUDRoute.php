<?php

namespace Frugal\Core\Router;

use Frugal\Core\Controllers\CrudController;
use Frugal\Core\Mappers\CRUDEnum;
use Frugal\Core\Mappers\HTTPVerb;

class CRUDRoute extends Route
{
    public CRUDEnum $action;
    public string $entityClassName;
    public ?string $payloadClassName = null;

    public function __construct(
        string $uri,
        HTTPVerb $verb,
        string $entityClassName,
        ?string $payloadClassName = null,
        ?string $handler = CrudController::class,
    )
    {
        $this->action = CRUDEnum::getCRUDActionFromHttpVerb($verb);
        $this->entityClassName = $entityClassName;
        $this->payloadClassName = $payloadClassName;
        parent::__construct(
            handler: $handler,
            uri: $uri, 
            verb: $verb
        );
    }
}