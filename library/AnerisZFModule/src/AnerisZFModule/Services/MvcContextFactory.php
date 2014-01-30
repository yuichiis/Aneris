<?php
namespace AnerisZFModule\Services;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Aneris\Container\ModuleManager;
use Aneris\Mvc\PluginManager;
use Aneris\Mvc\Context;
use Aneris\Http\Request as HttpRequest;
use Aneris\Http\Response as HttpResponse;

class MvcContextFactory implements FactoryInterface
{
    const ANERIS_MODULE_MANAGER = 'Aneris\Container\ModuleManager';

	protected $moduleManager;

    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $this->moduleManager = $serviceManager->get(self::ANERIS_MODULE_MANAGER);
        return $this;
    }

    public function newContext()
    {
        $serviceManager = $this->moduleManager->getServiceLocator();
        $pluginManager = new PluginManager($serviceManager);
        $context = new Context(
            new HttpRequest(),
            new HttpResponse(),
            null,
            $serviceManager,
            $pluginManager
        );
        $config = $serviceManager->get('config');
        if(isset($config['mvc']['plugins']))
            $pluginConfig = $config['mvc']['plugins'];
        else
            $pluginConfig = null;
        $pluginManager->setConfig($pluginConfig,$context);
        $router = $serviceManager->get('Aneris\Mvc\Router');
        if(isset($config['mvc']['router']))
            $router->setConfig($config['mvc']['router']);
        $context->setRouter($router);
        return $context;
    }
}