<?php
namespace Aneris\Container;

class InstanceManager
{
    protected $instances = array();

    public function setConfig($config)
    {
    }

    public function setInstance($className,$instances)
    {
        if(isset($this->instances[$className]))
            throw new Exception\DomainException('Already registored:'.$className);
        $this->instances[$className] = $instances;
    }

    public function has($className)
    {
        return array_key_exists($className, $this->instances);
    }

    public function get($className)
    {
        if(array_key_exists($className, $this->instances))
            return $this->instances[$className];
        return false;
    }
}
