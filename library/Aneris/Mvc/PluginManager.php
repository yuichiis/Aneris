<?php
namespace Aneris\Mvc;

use Aneris\Container\Container;
use Aneris\Container\ServiceLocatorInterface;
use Aneris\Container\Exception\DomainException as ServiceLocatorException;

class PluginManager extends Container
{
    public function __construct($serviceLocator=null)
    {
        parent::__construct();
        if($serviceLocator)
            $this->setParentManager($serviceLocator);
    }

    public function setConfig($config,$context=null)
    {
        if($config) {
            $components = array();
            foreach ($config as $name => $className) {
                $components[$name] = array('factory'=>$className.'::factory');
            }
            parent::setConfig(array('components'=>$components,'enable_cache'=>false));
        }
        if($context)
            $this->setInstance('Context',$context);
    }

    public function get($name)
    {
        try {
            return parent::get($name);
        }
        catch(ServiceLocatorException $e) {
            throw new Exception\DomainException('plugin is not found.: "'.$name.'"',0,$e);
        }
    }
}