<?php
namespace AcmeTest\Module1;

class Module
{
    public function getConfig()
    {
    	$namespace = __NAMESPACE__;
        return require __DIR__.'/Resources/config/module.config.php';
    }
}
