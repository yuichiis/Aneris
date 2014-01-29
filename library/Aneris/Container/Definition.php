<?php
namespace Aneris\Container;

use ReflectionClass;
use ReflectionMethod;
use ReflectionException;
use Aneris\Container\Exception;
use Aneris\Annotation\AnnotationManager;
use Aneris\Container\Annotations\Inject;
use Aneris\Container\Annotations\Named;
use Aneris\Container\Annotations\Scope;
use Aneris\Container\Annotations\Lazy;
use Aneris\Container\Annotations\Proxy;
use Aneris\Container\Annotations\PostConstruct;

class Definition
{
    const PROPERTY_ACCESS_POLICY = 'Aneris\Stdlib\Entity\PropertyAccessPolicyInterface';
    protected $className;
    protected $name;
    protected $constructor;
    protected $injects;
    protected $scope;
    protected $initMethod;
    protected $lazy;
    protected $proxyMode;

    public function __construct($classNameOfConfig,$annotationManager=null)
    {
        if(is_string($classNameOfConfig)) {
            $this->compile($classNameOfConfig,$annotationManager);
        } else {
            $this->load($classNameOfConfig);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getConstructor()
    {
        return $this->constructor;
    }

    public function getInjects()
    {
        return $this->injects;
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

    protected function load($config)
    {
        if(!isset($config['class']))
            throw new Exception\DomainException('A class name is not found in configuration');
        if(!is_array($config))
            throw new Exception\InvalidArgumentException('A configuration is invalid type value');
        $this->className = $config['class'];
        if(array_key_exists('constructor', $config))
            $this->constructor = $config['constructor'];
        else
            $this->constructor = null;
        if ($this->injects==null)
            $this->injects = array();
        if(array_key_exists('injects', $config))
            $this->mergeInjects($config['injects']);
    }

    protected function mergeInjects(array $injects)
    {
        foreach($injects as $methodName => $inject) {
            foreach($inject as $paramName => $reference) {
                $this->injects[$methodName][$paramName] = $reference;
            }
        }
    }

    public function export()
    {
        $definition['class'] = $this->className;
        if($this->name)
            $definition['name'] = $this->name;
        $definition['constructor'] = $this->constructor;
        $definition['injects'] = $this->injects;
        if($this->scope)
            $definition['scope'] = $this->scope;
        if($this->initMethod)
            $definition['init_method'] = $this->initMethod;
        return $definition;
    }

    protected function compile($className,$annotationManager=null)
    {
        $this->className = $className;
        $this->constructor = null;
        $this->injects = array();

        try {
            $reflection = new ReflectionClass($this->className);
        } catch(ReflectionException $e) {
            throw new Exception\DomainException($this->className.' does not exist',0,$e);
        }

        $methodRef = $reflection->getConstructor();
        if($methodRef && !($methodRef->getDeclaringClass()->isInternal())) {
            $this->constructor = $methodRef->name;
            $this->injects[$this->constructor] = $this->getParameters($methodRef);
        }

        if($annotationManager) {
            $this->mergeAnnotations($annotationManager,$reflection);
        }
    }

    protected function getParameters($methodRef)
    {
        $params = array();
        $paramRefs = $methodRef->getParameters();
        foreach($paramRefs as $paramRef) {
            $param = array();
            $paramName = $paramRef->name;
            try {
                $paramClassRef = $paramRef->getClass();
                if($paramClassRef)
                    $param[InjectTypeInterface::ARGUMENT_REFERENCE] = $paramClassRef->getName();
                if($paramRef->isOptional()) {
                    $param[InjectTypeInterface::ARGUMENT_DEFAULT] = $paramRef->getDefaultValue();
                }
            } catch(ReflectionException $e) {
                throw new Exception\DomainException('invalid type of parameter "'.$paramName.'". reason: '.$e->getMessage().' : '.$methodRef->getFileName().'('.$methodRef->getStartLine().')',0,$e);
            }
            $params[$paramName] = $param;
        }
        return $params;
    }

    protected function mergeAnnotations($annotationManager,$classRef)
    {
        $annotations = $annotationManager->getClassAnnotations($classRef);
        foreach ($annotations as $annotation) {
            if($annotation instanceof Named)
                $this->name = $annotation->value;
            if($annotation instanceof Scope)
                $this->scope = $annotation->value;
            if($annotation instanceof Lazy)
                $this->lazy = true;
            if($annotation instanceof Proxy)
                $this->proxyMode = $annotation->value;
        }
        foreach ($classRef->getProperties() as $propRef) {
            $annotations = $annotationManager->getPropertyAnnotations($propRef);
            foreach ($annotations as $annotation) {
                if(!($annotation instanceof Inject))
                    continue;
                $setter = 'set'.ucfirst($propRef->name);
                if($classRef->hasMethod($setter)) {
                    $params = $this->getParameters($classRef->getMethod($setter));
                } else {
                    if($classRef->hasMethod('__call') || $classRef->isSubclassOf(self::PROPERTY_ACCESS_POLICY))
                        $params = array($propRef->name => array());
                    else
                        throw new Exception\DomainException('setter is not found to inject for "'.$propRef->name.'": '.$classRef->getFilename().'('.$classRef->getStartLine().')');
                }
                if($annotation->value) {
                    foreach ($annotation->value as $named) {
                        if($named instanceof Named) {
                            if($named->parameter)
                                $params[$named->parameter][InjectTypeInterface::ARGUMENT_REFERENCE] = $named->value;
                            else
                                $params[$propRef->name][InjectTypeInterface::ARGUMENT_REFERENCE] = $named->value;
                        }
                    }
                }
                $this->injects[$setter] = $params;
            }
        }

        foreach ($classRef->getMethods() as $methodRef) {
            $annotations = $annotationManager->getMethodAnnotations($methodRef);
            foreach ($annotations as $annotation) {
                if($annotation instanceof PostConstruct) {
                    if($this->initMethod)
                        throw new Exception('@PostConstruct is used twice or more.: '.$methodRef->getFilename().'('.$methodRef->getStartLine().')');
                    $this->initMethod = $methodRef->name;
                    continue;
                }
                if(!($annotation instanceof Inject))
                    continue;
                if($this->constructor == $methodRef->name)
                    $params = $this->injects[$this->constructor];
                else
                    $params = $this->getParameters($methodRef);
                if($annotation->value) {
                    foreach ($annotation->value as $named) {
                        if($named instanceof Named) {
                            if($named->parameter==null)
                                throw new Exception\DomainException('argument name is not found specified for @Named on "'.$methodRef->name.'": '.$methodRef->getFilename().'('.$methodRef->getStartLine().')');
                            $params[$named->parameter][InjectTypeInterface::ARGUMENT_REFERENCE] = $named->value;
                        }
                    }
                }
                $this->injects[$methodRef->name] = $params;
            }
        }
    }

    public function addMethod($methodName)
    {
        $methodRef = null;
        try {
            $reflection = new ReflectionClass($this->className);
            if($reflection->hasMethod($methodName))
                $methodRef = $reflection->getMethod($methodName);
        } catch(ReflectionException $e) {
            throw new Exception\DomainException($this->className.' does not exist',0,$e);
        }

        if($methodRef && !($methodRef->getDeclaringClass()->isInternal())) {
            $this->injects[$methodName] = $this->getParameters($methodRef);
            return true;
        } else {
            return false;
        }
    }

    public function addMethodForce($methodName,$paramName,$reference=null)
    {
        if($reference)
            $this->injects[$methodName][$paramName][InjectTypeInterface::ARGUMENT_REFERENCE] = $reference;
        else
            $this->injects[$methodName][$paramName] = array();
    }
}
