<?php
namespace Aneris\Validator;

interface ConstraintValidatorFactoryInterface
{
	public function getInstance($key);
}