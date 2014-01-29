<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintValidatorInterface;
use Aneris\Validator\ConstraintInterface;
use Aneris\Validator\ConstraintValidatorContextInterface;

class DigitsValidator implements ConstraintValidatorInterface
{
    protected $integer;
    protected $fraction;

    public function initialize(ConstraintInterface $constraint)
    {
        $this->integer  = $constraint->integer;
        $this->fraction = $constraint->fraction;
    }

    public function isValid($value, ConstraintValidatorContextInterface $context)
    {
        if($value===null || $value==='')
            return true;
        $parts = explode('.', $value);
        if(count($parts)>2)
            return false;
        $integerPartLen = strlen($parts[0]);
        if($integerPartLen>0 && !ctype_digit($parts[0]))
            return false;
        if($integerPartLen > $this->integer)
            return false;

        if(!isset($parts[1]))
            return true;
        $fractionPartLen = strlen($parts[1]);
        if($fractionPartLen>0 && !ctype_digit($parts[1]))
            return false;
        if($fractionPartLen > $this->fraction)
            return false;
        return true;
    }
}
