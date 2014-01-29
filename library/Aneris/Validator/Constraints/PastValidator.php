<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintValidatorInterface;
use Aneris\Validator\ConstraintInterface;
use Aneris\Validator\ConstraintValidatorContextInterface;
use DateTime;

class PastValidator implements ConstraintValidatorInterface
{
    public function initialize(ConstraintInterface $constraint)
    {
    }

    public function isValid($value, ConstraintValidatorContextInterface $context)
    {
        if($value===null || $value==='')
            return true;

        $value = DateFormatValidationLibrary::convertToDateTime($value);
        if($value===false)
            return false;

        $interval = $value->diff(new DateTime('now'));

        return ($interval->invert==0);
    }
}
