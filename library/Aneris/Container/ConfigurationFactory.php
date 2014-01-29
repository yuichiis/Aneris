<?php
namespace Aneris\Container;

class ConfigurationFactory
{
	public static function factory(ServiceLocatorInterface $serviceLocator=null,$componentName=null,$args=null)
	{
		$config = $serviceLocator->get('config');
        if(isset($args['config']))
        	$componentName = $args['config'];
        $paths = explode('::', $componentName);
        foreach ($paths as $path) {
        	if(!isset($config[$path]))
        		return null;
        	$config = $config[$path];
        }
        return $config;
	}
}