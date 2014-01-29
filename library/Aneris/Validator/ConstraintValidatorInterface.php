<?php
namespace Aneris\Validator;

interface ConstraintValidatorInterface
{
    public function initialize(ConstraintInterface $constraint);

  	public function isValid($value, ConstraintValidatorContextInterface $context);
}