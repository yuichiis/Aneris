<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintValidatorInterface;
use Aneris\Validator\ConstraintInterface;
use Aneris\Validator\ConstraintValidatorContextInterface;
use DateTime;

class DateValidator implements ConstraintValidatorInterface
{
    protected $format;

    public function initialize(ConstraintInterface $constraint)
    {
        $this->format  = $constraint->format;
    }

    public function isValid($value, ConstraintValidatorContextInterface $context)
    {
        if($value===null || $value==='')
            return true;
        if($value instanceof DateTime)
            return true;

        return DateFormatValidationLibrary::isDate($value);
    }
}
