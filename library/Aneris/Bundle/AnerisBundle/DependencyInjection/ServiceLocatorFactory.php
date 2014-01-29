<?php
namespace Aneris\Bundle\AnerisBundle\DependencyInjection;

use Aneris\Container\ModuleManager;
use Aneris\Bundle\AnerisBundle\Exception;

class ServiceLocatorFactory
{
	public static function factory(ModuleManager $moduleManager)
	{
		if($moduleManager==null)
			throw new Exception\DomainException('moduleManage is not specified.');
		return $moduleManager->getServiceLocator();
	}
}
