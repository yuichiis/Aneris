<?php
namespace AnerisZFModule\Services;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Aneris\Container\ModuleManager;
use Aneris\Container\ServiceLocatorProxy;

class ModuleManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $config = $serviceManager->get('Config');
        if(isset($config['aneris']))
            $config = $config['aneris'];
        else
            $config = array();
        $moduleManager = new ModuleManager($config);
        $serviceManagerProxy = new ServiceLocatorProxy($serviceManager);
        $moduleManager->getServiceLocator()->setParentManager($serviceManagerProxy);
        return $moduleManager;
    }
}