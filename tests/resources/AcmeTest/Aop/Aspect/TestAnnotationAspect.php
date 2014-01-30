<?php
namespace AcmeTest\Aop\Aspect;

use Aneris\Aop\Annotations\Aspect;
use Aneris\Aop\Annotations\Before;

/**
* @Aspect
*/
class TestAnnotationAspect
{
	/**
	* @Before("execution(*::Log1())")
	*/
	public function foo1($event)
	{
		return __METHOD__;
	}
	/**
	* @Before("execution(*::Log2())")
	*/
	public function foo2($event)
	{
		return __METHOD__;
	}
}