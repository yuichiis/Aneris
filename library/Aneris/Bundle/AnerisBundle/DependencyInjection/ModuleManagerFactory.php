<?php
namespace Aneris\Bundle\AnerisBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Aneris\Bundle\AnerisBundle\Exception;
use Aneris\Container\ModuleManager;
use Aneris\Container\ServiceLocatorProxy;

class ModuleManagerFactory
{
	public static function factory(ContainerInterface $container, $config_path=null,$environment=null)
	{
		if(!file_exists($config_path))
			throw new Exception\DomainException('file not exist: '.$config_path);
		$config = require $config_path;
		if(!isset($config['aneris']))
			throw new Exception\DomainException('configuration for aneris is not found in '.$config_path);
		$moduleManager = new ModuleManager($config['aneris'],$environment);
		if($container==null)
			throw new Exception\DomainException('Symfony container is not specified.');
        $serviceLocatorProxy = new ServiceLocatorProxy($container);
        $moduleManager->getServiceLocator()->setParentManager($serviceLocatorProxy);
        return $moduleManager;
	}
}
