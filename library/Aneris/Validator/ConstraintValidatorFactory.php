<?php
namespace Aneris\Validator;

class ConstraintValidatorFactory implements ConstraintValidatorFactoryInterface
{
	protected $constraintValidators;

	public function getInstance($validatorName)
	{
        if(isset($this->constraintValidators[$validatorName])) {
            return $this->constraintValidators[$validatorName];
        }
        if(!class_exists($validatorName))
            throw new Exception\DomainException('a class is not found:'.$validatorName);
        $validator = new $validatorName();
        $this->constraintValidators[$validatorName] = $validator;
        return $validator;
	}

    public function newInstance($validatorName)
    {
        if(!class_exists($validatorName))
            throw new Exception\DomainException('a class is not found:'.$validatorName);
        return new $validatorName();
    }
}