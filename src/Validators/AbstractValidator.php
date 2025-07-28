<?php

namespace Frugal\Core\Validators;

use InvalidArgumentException;

abstract class AbstractValidator
{
    public function validate(array $jsonDecoded) : void
    {
        $this->checkMandatoryFields($jsonDecoded);
    }

    private function checkMandatoryFields(array $jsonDecoded)
    {
        foreach($this->getMandatoryFields() as $field) {
            if(!isset($jsonDecoded[$field])) {
                throw new InvalidArgumentException("Field $field is mandatory");
            }
        }
    }

    abstract public function getMandatoryFields() : array;
}