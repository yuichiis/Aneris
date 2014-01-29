<?php
namespace Aneris\Container;

class ComponentDefinition
{
    protected $name;
    protected $class;
    protected $injects;
    protected $factory;
    protected $factoryArgs;
    protected $scope;
    protected $initMethod;
    protected $lazy;
    protected $proxyMode;

    public function __construct(array $config=null)
    {
        if(isset($config['name']))
            $this->name = $config['name'];
        if(isset($config['class']))
            $this->class = $config['class'];
        if(isset($config['injects']))
            $this->injects = $config['injects'];
        if(isset($config['constructor_args']))
            $this->setConstructorArgs($config['constructor_args']);
        if(isset($config['properties']))
            $this->setProperties($config['properties']);
        if(isset($config['factory']))
            $this->factory = $config['factory'];
        if(isset($config['factory_args']))
            $this->factoryArgs = $config['factory_args'];
        if(isset($config['scope']))
            $this->scope = $config['scope'];
        if(isset($config['init_method']))
            $this->initMethod = $config['init_method'];
        if(isset($config['lazy']))
            $this->lazy = $config['lazy'];
        if(isset($config['proxy']))
            $this->proxyMode = $config['proxy'];
    }

    protected function setConstructorArgs(array $properties)
    {
        $this->injects['__construct'] = $properties;
    }

    protected function setProperties(array $properties)
    {
        foreach ($properties as $name => $arg) {
            $this->injects['set'.ucfirst($name)] = array($name => $arg);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getClassName()
    {
        return $this->class;
    }

    public function getInjects()
    {
        if($this->injects==null)
            return array();
        return $this->injects;
    }

    public function getInject($name)
    {
        if(!isset($this->injects[$name]))
            return false;
        return $this->injects[$name];
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getInitMethod()
    {
        return $this->initMethod;
    }

    public function isLazy()
    {
        return ($this->lazy==true);
    }

    public function getProxyMode()
    {
        return $this->proxyMode;
    }

    public function hasFactory()
    {
        return isset($this->factory);
    }

    public function getFactory()
    {
        if($this->factory==null)
            return null;
        return $this->factory;
    }

    public function getFactoryArgs()
    {
        return $this->factoryArgs;
    }

    public function hasClosureFactory()
    {
        if(isset($this->factory) && !is_string($this->factory))
            return true;
        return false;
    }

    public function addPropertyWithReference($name,$ref)
    {
        if(!is_string($ref))
            throw new Exception\InvalidArgumentException('referenece must be a string as class name or compornent name.');
        $this->injects['set'.ucfirst($name)][$name][InjectTypeInterface::ARGUMENT_REFERENCE] = $ref;
        unset($this->injects['set'.ucfirst($name)][$name][InjectTypeInterface::ARGUMENT_VALUE]);
    }

    public function addPropertyWithValue($name,$value)
    {
        $this->injects['set'.ucfirst($name)][$name][InjectTypeInterface::ARGUMENT_VALUE] = $value;
        unset($this->injects['set'.ucfirst($name)][$name][InjectTypeInterface::ARGUMENT_REFERENCE]);
    }

    public function addConstructorArgWithReference($name,$ref)
    {
        if(!is_string($ref))
            throw new Exception\InvalidArgumentException('referenece must be a string as class name or compornent name.');
        $this->injects['__construct'][$name][InjectTypeInterface::ARGUMENT_REFERENCE] = $ref;
        unset($this->injects['__construct'][$name][InjectTypeInterface::ARGUMENT_VALUE]);
    }

    public function addConstructorArgWithValue($name,$value)
    {
        $this->injects['__construct'][$name][InjectTypeInterface::ARGUMENT_VALUE] = $value;
        unset($this->injects['__construct'][$name][InjectTypeInterface::ARGUMENT_REFERENCE]);
    }
}