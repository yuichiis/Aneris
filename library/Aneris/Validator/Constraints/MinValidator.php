<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintValidatorInterface;
use Aneris\Validator\ConstraintInterface;
use Aneris\Validator\ConstraintValidatorContextInterface;

class MinValidator implements ConstraintValidatorInterface
{
    protected $min;
    
    public function initialize(ConstraintInterface $constraint)
    {
        $this->min = $constraint->value;
    }

    public function isValid($value, ConstraintValidatorContextInterface $context)
    {
        if($value===null || $value==='')
            return true;
        return ($value >= $this->min);
    }
}
