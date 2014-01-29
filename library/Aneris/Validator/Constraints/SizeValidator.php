<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintValidatorInterface;
use Aneris\Validator\ConstraintInterface;
use Aneris\Validator\ConstraintValidatorContextInterface;

class SizeValidator implements ConstraintValidatorInterface
{
    protected $min;
    protected $max;
    
    public function initialize(ConstraintInterface $constraint)
    {
        $this->min = $constraint->min;
        $this->max = $constraint->max;
    }

    public function isValid($value, ConstraintValidatorContextInterface $context)
    {
        if($value===null || $value==='')
            return true;
        
        if(is_string($value)) {
            $size = strlen($value);
        } else if(is_array($value)) {
            $size = count($value);
        } else {
            return false;
        }

        if($this->max !== null && $size > $this->max)
            return false;
        if($this->min !== null && $size < $this->min)
            return false;
        return true;
    }
}
