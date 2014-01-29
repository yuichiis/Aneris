<?php
namespace Aneris\Container;

class ServiceLocatorProxy implements ServiceLocatorInterface
{
	protected $oppositeServiceLocator;

    public function __construct($oppositeServiceLocator)
    {
    	$this->oppositeServiceLocator = $oppositeServiceLocator;
    }

    public function get($className)
    {
    	return $this->oppositeServiceLocator->get($className);
    }

    public function has($className)
    {
    	return $this->oppositeServiceLocator->has($className);
    }
}
