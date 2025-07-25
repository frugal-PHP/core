<?php

namespace Frugal\Core\Validators;

use InvalidArgumentException;

abstract class AbstractValidator
{
    public function validate(array $jsonDecoded) : void
    {
        $this->checkMandatoryFields();
    }

    public function checkMandatoryFields()
    {
        foreach($this->getMandatoryFields() as $field) {
            if(!isset($jsonDecoded[$field])) {
                throw new InvalidArgumentException("Field $field is mandatory");
            }
        }
    }

    abstract public function getMandatoryFields() : array;
}