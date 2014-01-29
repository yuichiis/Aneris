<?php
namespace Aneris\Validator;

class ConstraintViolation implements ConstraintViolationInterface
{
	protected $messageTemplate;
	protected $message;
	protected $rootBean;
	protected $invalidValue;
	protected $propertyPath;

	public function __construct(
		$messageTemplate,
		$interpolatedMessage,
		$rootBean,
		$invalidValue,
		$propertyPath
		)
	{
		$this->messageTemplate = $messageTemplate;
		$this->message         = $interpolatedMessage;
		$this->rootBean        = $rootBean;
		$this->invalidValue    = $invalidValue;
		$this->propertyPath    = $propertyPath;
	}

	public function getMessage()
	{
		return $this->message;
	}	
	
	public function getMessageTemplate()
	{
		return $this->messageTemplate;
	}	
	
	public function getPropertyPath()
	{
		return $this->propertyPath;
	}	

	public function getRootBean()
	{
		return $this->rootBean;
	}	

	public function getInvalidValue()
	{
		return $this->invalidValue;
	}	
}