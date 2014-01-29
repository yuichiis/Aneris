<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintValidatorInterface;
use Aneris\Validator\ConstraintInterface;
use Aneris\Validator\ConstraintValidatorContextInterface;

class NotBlankValidator implements ConstraintValidatorInterface
{
    public function initialize(ConstraintInterface $constraint)
    {
    }

    public function isValid($value, ConstraintValidatorContextInterface $context)
    {
        if($value===null)
            return true;
        if(!is_string($value))
            return true;
        return (strlen($value) > 0);
    }
}
