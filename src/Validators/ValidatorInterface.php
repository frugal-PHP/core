<?php

namespace Frugal\Core\Validators;

interface ValidatorInterface {
        public static function validate(array $jsonDecoded) : void;
}