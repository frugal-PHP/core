<?php

namespace Frugal\Core\Mappers;

enum CRUDEnum
{
    case CREATE;
    case RETRIEVE;
    case UPDATE;
    case DELETE;
}