<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintValidatorInterface;
use Aneris\Validator\ConstraintInterface;
use Aneris\Validator\ConstraintValidatorContextInterface;

class NullValidator implements ConstraintValidatorInterface
{
    public function initialize(ConstraintInterface $constraint)
    {
    }

    public function isValid($value, ConstraintValidatorContextInterface $context)
    {
        return ($value === null);
    }
}
