<?php
namespace Aneris\Validator;

interface ValidatorInterface
{
    public function validate($object, $groups=null);

	public function validateProperty($object,$propertyName,$groups=null);

	public function validateValue($className,$propertyName,$value,$groups=null);

	public function getConstraintsForClass($className);
}
