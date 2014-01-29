<?php
namespace Aneris\Aop;

class EventListener
{
    protected $callback;
    protected $className;
    protected $methodName;

	public function __construct($callback=null,$className=null,$methodName=null)
	{
		$this->callback = $callback;
		$this->className = $className;
		$this->methodName = $methodName;
	}

	public function getCallBack()
	{
		return $this->callback;
	}

	public function setCallBack($callback)
	{
		$this->callback = $callback;
    	return $this;
	}

	public function getClassName()
	{
		return $this->className;
	}

	public function getMethodName()
	{
		return $this->methodName;
	}
}