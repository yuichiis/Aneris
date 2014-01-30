<?php
namespace AcmeTest\Module2;

class AutorunTest
{
	protected $obj;

	public function __construct(AutorunTestInjection $obj)
	{
		$this->obj = $obj;
	}
	public function invoke($moduleManager)
	{
		return $this->obj->invoke();
	}
}