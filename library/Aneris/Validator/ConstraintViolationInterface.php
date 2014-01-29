<?php
namespace Aneris\Validator;

interface ConstraintViolationInterface {

	public function getMessage();

	public function getMessageTemplate();

	public function getRootBean();

	public function getPropertyPath();

	public function getInvalidValue();
}