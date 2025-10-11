<?php

namespace Frugal\Core\Mappers;

enum CRUDEnum
{
    case CREATE;
    case RETRIEVE;
    case UPDATE;
    case DELETE;

    public static function getCRUDActionFromHttpVerb(HTTPVerb $verb)
    {
        return match($verb) {
            HTTPVerb::POST   => self::CREATE,
            HTTPVerb::GET    => self::RETRIEVE,
            HTTPVerb::PUT    => self::UPDATE,
            HTTPVerb::PATCH  => self::UPDATE,
            HTTPVerb::DELETE => self::DELETE,
            default           => throw new \InvalidArgumentException("HTTP verb {$verb->name} has no CRUD equivalent.")
        };
    }
}
