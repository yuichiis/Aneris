<?php
namespace AnerisZFModule;

class Module
{

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getConfig($env = null)
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                // On Zend Framework 2
                'Aneris\Validator\Validator'     => 'AnerisZFModule\Services\ValidatorFactory',
                'Aneris\Form\FormContextBuilder' => 'AnerisZFModule\Services\FormContextBuilderFactory',
                'Aneris\Form\View\FormRenderer'  => 'AnerisZFModule\Services\FormRendererFactory',
                'Aneris\Container\ModuleManager' => 'AnerisZFModule\Services\ModuleManagerFactory',
                // On Aneris
                'AnerisServiceLocator' => function ($sm) {
                    return $sm->get('Aneris\Container\ModuleManager')->getServiceLocator();
                },
                'AnerisMvcContextFactory'    => 'AnerisZFModule\Services\MvcContextFactory',
            ),
        );
    }
}