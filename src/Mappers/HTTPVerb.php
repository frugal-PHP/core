<?php

namespace Frugal\Core\Mappers;

enum HTTPVerb : string
{
    case GET = 'get';
    case POST = 'post';
    case PUT = 'put';
    case PATCH = 'patch';
    case DELETE = 'delete';
    case OPTIONS = 'options';
    case HEAD = 'head';
}