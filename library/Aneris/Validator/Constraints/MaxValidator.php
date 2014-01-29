<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintValidatorInterface;
use Aneris\Validator\ConstraintInterface;
use Aneris\Validator\ConstraintValidatorContextInterface;

class MaxValidator implements ConstraintValidatorInterface
{
    protected $max;

    public function initialize(ConstraintInterface $constraint)
    {
        $this->max = $constraint->value;
    }

    public function isValid($value, ConstraintValidatorContextInterface $context)
    {
        if($value===null || $value==='')
            return true;
        return ($value <= $this->max);
    }
}
