<?php

namespace Frugal\Core\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface PayloadInterface 
{
    public static function fromRequest(ServerRequestInterface $request) : self;
    public static function checkMandatory(array $payload) : void;
    public function getRequestBodyArray() : array;
}