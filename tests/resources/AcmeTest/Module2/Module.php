<?php
namespace AcmeTest\Module2;

class Module
{
    public function getConfig()
    {
    	$namespace = __NAMESPACE__;
        return require __DIR__.'/Resources/config/module.config.php';
    }

    public function invoke($moduleManager)
    {
    	$config = $moduleManager->getServiceLocator()->get('config');
    	$method = $config['global_config']['execute'];
    	return $this->$method($moduleManager);
    }

    private function moduleTestRunNormal()
    {
    	return __CLASS__;
    }

    private function moduleTestRunGetServiceLocator($moduleManager)
    {
        return $moduleManager->getServiceLocator();
    }
/*
    private function moduleTestRunGetDi($moduleManager)
    {
        return $moduleManager->getServiceLocator()->get('Di');
    }
*/
}
