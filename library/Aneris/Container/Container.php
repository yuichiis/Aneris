<?php
namespace Aneris\Container;

use Aneris\Container\Exception;
use Aneris\Container\ServiceManager;
use Aneris\Stdlib\Entity\PropertyAccessPolicyInterface;
use Aneris\Annotation\AnnotationManager;

use ReflectionClass;

class Container implements ServiceLocatorInterface
{
    const DEFAULT_ANNOTATION_MANAGER = 'Aneris\Annotation\AnnotationManager';
    protected $definitionManager;
    protected $instanceManager;
    protected $parentManager;
    protected $annotationManager;
    protected $aopManager;
    protected $proxyManager;
    protected $componentPaths;
    protected $autoProxy='component';

    public function __construct(
        array $config = null,
        ComponentDefinitionManager $componentManager=null,
        DefinitionManager $definitionManager=null,
        InstanceManager $instanceManager=null)
    {
        if($componentManager)
            $this->componentManager = $componentManager;
        else
            $this->componentManager = new ComponentDefinitionManager();
        if($definitionManager)
            $this->definitionManager = $definitionManager;
        else
            $this->definitionManager = new DefinitionManager();
        if($instanceManager)
            $this->instanceManager = $instanceManager;
        else
            $this->instanceManager = new InstanceManager();
        if($config!==null)
            $this->setConfig($config);
    }

    public function getComponentManager()
    {
        return $this->componentManager;
    }

    public function getDefinitionManager()
    {
        return $this->definitionManager;
    }

    public function getInstanceManager()
    {
        return $this->instanceManager;
    }

    public function setParentManager(ServiceLocatorInterface $parentManager)
    {
        $this->parentManager = $parentManager;
        return $this;
    }

    public function getParentManager()
    {
        return $this->parentManager;
    }

    public function setConfig($config)
    {
        if(array_key_exists('annotation_manager',$config)) {
            $this->setAnnotationManagerName($config['annotation_manager']);
            $this->definitionManager->setAnnotationManager($this->annotationManager);
        }
        $this->componentManager->setConfig($config);
        $this->definitionManager->setConfig($config);
        $this->instanceManager->setConfig($config);
        if($this->proxyManager) {
            $this->proxyManager>setConfig($config);
        }
        if(isset($config['component_paths'])) {
            $this->componentPaths = $config['component_paths'];
        }
        if(isset($config['auto_proxy'])) {
            $this->autoProxy = $config['auto_proxy'];
        }
        return $this;
    }

    public function setProxyManager(ProxyManagerInterface $proxyManager)
    {
        $this->proxyManager = $proxyManager;
        return $this;
    }

    public function setAnnotationManagerName($annotationManager)
    {
        if($annotationManager===true || $annotationManager === self::DEFAULT_ANNOTATION_MANAGER) {
            $this->annotationManager = AnnotationManager::factory();
        } else if(is_string($annotationManager)) {
            if(class_exists($annotationManager))
                $this->$annotationManager = new $annotationManager();
        } else {
            $this->annotationManager = $annotationManager;
        }
        return $this;
    }

    public function getAnnotationManager()
    {
        return $this->annotationManager;
    }

    public function scanComponents()
    {
        if($this->componentPaths==null)
            return $this;

        $componentScanner = new ComponentScanner();
        $componentScanner->setAnnotationManager($this->annotationManager);
        $this->componentManager->attachScanner($componentScanner);
        if($this->proxyManager) {
            $this->proxyManager->attachScanner($componentScanner);
        }
        $componentScanner->scan($this->componentPaths);
        return $this;
    }

    public function get($componentName)
    {
        if(!is_string($componentName))
            throw new Exception\InvalidArgumentException('Class name must be string');

        $componentName = $this->componentManager->resolveAlias($componentName);
        $component = $this->componentManager->getComponent($componentName);

        if($this->instanceManager->has($componentName)) {
            if(!$component || $component->getScope()!=InjectTypeInterface::SCOPE_PROTOTYPE)
                return $this->instanceManager->get($componentName);
        }

        $isDefinedComponent = false;
        if($component) {
            $isDefinedComponent = true;
        } else {
            if(class_exists($componentName)) {
                $component = $this->componentManager->newComponent($componentName);
            } else {
                if($this->parentManager==null)
                    throw new Exception\DomainException('Undefined component.: '.$componentName);
                return $this->parentManager->get($componentName);
            }
        }

        if($component->hasFactory()) {
            $definition = null;
        } else {
            $definition = $this->definitionManager->getDefinition($component->getClassName());
            if($definition->getName())
                $isDefinedComponent = true;
        }
        $autoProxy = false;
        $proxyOptions = null;
        if($this->proxyManager) {
            if($component->isLazy() || ($definition && $definition->isLazy())) {
                $autoProxy = true;
                $proxyOptions['lazy'] = true;
            } else if($this->autoProxy == 'component') {
                if($isDefinedComponent)
                    $autoProxy = true;
            } else if($this->autoProxy == 'explicit') {
                if($component->getProxyMode() || ($definition && $definition->getProxyMode())) {
                    $autoProxy = true;
                }
            } else if($this->autoProxy == 'all') {
                $autoProxy = true;
            }
        }

        if($autoProxy) {
            $proxyOptions['mode'] = $component->getProxyMode();
            if(!isset($proxyOptions['mode']) && $definition) {
                $proxyOptions['mode'] = $definition->getProxyMode();
            }
            $instance = $this->proxyManager->newProxy($this,$component,$proxyOptions);
        } else {
            $instance = $this->instantiate($component,$componentName,$definition);
        }

        $scope = null;
        if($definition)
            $scope = $definition->getScope();
        if($component->getScope())
            $scope = $component->getScope();
        if($scope!=InjectTypeInterface::SCOPE_PROTOTYPE) {
            $this->instanceManager->setInstance($componentName,$instance);
        }
        return $instance;
    }

    public function instantiate(ComponentDefinition $component,$componentName=null,Definition $definition=null,$instance=null,$alternateConstructor=null)
    {
        if($component->hasFactory())
            return $this->newInstanceByFactory($component,$componentName);

        if($componentName==null)
            $componentName = $component->getName();
        if($definition==null)
            $definition = $this->definitionManager->getDefinition($component->getClassName());
        $injects = $definition->getInjects();
        $constructor = $definition->getConstructor();
        if($alternateConstructor==null)
            $alternateConstructor=$constructor;
        if($constructor) {
            $params = $this->buildParams($injects[$constructor],$componentName,$constructor,$component->getInject('__construct'));
        } else {
            $params = array();
        }
        if($instance==null)
            $instance = $this->newInstanceByParams($component->getClassName(),$params,$componentName);
        else if($constructor)
            call_user_func_array(array($instance,$alternateConstructor), $params);

        $componentInjects = $component->getInjects();
        $setterInjects = array_merge($injects,$componentInjects);
        foreach(array_keys($setterInjects) as $methodName) {
            if($methodName===$constructor || $methodName===$alternateConstructor)
                continue;
            if(isset($componentInjects[$methodName]))
                $componentInject = $componentInjects[$methodName];
            else
                $componentInject = false;
            if(!isset($injects[$methodName])) {
                if(!$definition->addMethod($methodName)) {
                    foreach (array_keys($componentInject) as $paramName) {
                        $definition->addMethodForce($methodName,$paramName);
                    }
                }
                $injects = $definition->getInjects();
                $this->definitionManager->setDefinition($component->getClassName(),$definition);
            }
            $params = $this->buildParams($injects[$methodName],$componentName,$methodName,$componentInject);
            $this->injectBySetter($instance,$methodName,$params);
        }

        $initMethod = $definition->getInitMethod();
        if($component->getInitMethod())
            $initMethod = $component->getInitMethod();
        if($initMethod) {
            $init = array($instance,$initMethod);
            if(!is_callable($init))
                throw new Exception\DomainException('Invalid initMethod in the component.:'.$componentName);
            call_user_func($init);
        }
        return $instance;
    }


    public function has($componentName)
    {
        if(!is_string($componentName))
            throw new Exception\InvalidArgumentException('Class name must be string');

        $componentManager = $this->componentManager;
        $instanceManager = $this->instanceManager;

        $componentName = $componentManager->resolveAlias($componentName);
        if($instanceManager->has($componentName))
            return true;
        if($componentManager->hasComponent($componentName))
            return true;

        if(class_exists($componentName))
            return true;
        if($this->parentManager==null)
            return false;
        return $this->parentManager->has($componentName);
    }

    protected function buildParams($inject,$componentName,$methodName,$componentInject)
    {
        $params = array();
        foreach($inject as $paramName => $paramSet) {
            list($type,$param) = $this->parseParam($paramSet,$componentName,$methodName,$paramName);
            if(isset($componentInject[$paramName]))  {
                list($type,$param) = $this->parseParam($componentInject[$paramName],$componentName,$methodName,$paramName);
                if($type===InjectTypeInterface::ARGUMENT_DEFAULT)
                    throw new Exception\DomainException('it can not use "default" in a component parameter:'.$componentName.'::'.$methodName.'( .. $'.$paramName.' .. )');
            }
            if($type==null)
                throw new Exception\DomainException('Undefined a specified class or instance for parameter:'.$componentName.'::'.$methodName.'( .. $'.$paramName.' .. )');
            if($type==InjectTypeInterface::ARGUMENT_REFERENCE) {
                if($param === null)
                    throw new Exception\DomainException('Undefined a specified class or instance for parameter:'.$componentName.'::'.$methodName.'( .. $'.$paramName.' .. )');
                if(array_key_exists(InjectTypeInterface::ARGUMENT_DEFAULT, $paramSet)) {
                    if($this->has($param))
                        $param = $this->get($param);
                    else
                        $param = $paramSet[InjectTypeInterface::ARGUMENT_DEFAULT];
                } else {
                    $param = $this->get($param);
                }
            }
            $params[] = $param;
        }
        return $params;
    }

    protected function parseParam($param,$componentName,$methodName,$paramName)
    {
        if(is_array($param)) {
            if(array_key_exists(InjectTypeInterface::ARGUMENT_VALUE, $param))
                $type = InjectTypeInterface::ARGUMENT_VALUE;
            else if(array_key_exists(InjectTypeInterface::ARGUMENT_DEFAULT, $param)) // CAUTION: default must be this position
                $type = InjectTypeInterface::ARGUMENT_DEFAULT;
            else if(array_key_exists(InjectTypeInterface::ARGUMENT_REFERENCE, $param))
                $type = InjectTypeInterface::ARGUMENT_REFERENCE;
            else
                return array(null,null);
            $param = $param[$type];
        } else if(is_string($param)) {
            $type = InjectTypeInterface::ARGUMENT_REFERENCE;
        } else {
            throw new Exception\DomainException('invalid definition of parameter:'.$componentName.'::'.$methodName.'( .. $'.$paramName.' .. )');
        }
        return array($type,$param);
    }

    protected function injectBySetter($instance,$methodName,$params)
    {
        if($instance instanceof PropertyAccessPolicyInterface) {
            $property = lcfirst(substr($methodName,3));
            $instance->$property = $params[0];
        } else {
            call_user_func_array(array($instance,$methodName), $params);
        }
    }

    protected function newInstanceByParams($className,$params,$componentName)
    {
        if(!class_exists($className)) {
            throw new Exception\DomainException('Undefined class "'.$className.'" in the component "'.$componentName.'"');
        }
        switch(count($params)) {
            case 0:
                $instance = new $className();
                break;
            case 1:
                $instance = new $className($params[0]);
                break;
            case 2:
                $instance = new $className($params[0],$params[1]);
                break;
            case 3:
                $instance = new $className($params[0],$params[1],$params[2]);
                break;
            case 4:
                $instance = new $className($params[0],$params[1],$params[2],$params[3]);
                break;
            case 5:
                $instance = new $className($params[0],$params[1],$params[2],$params[3],$params[4]);
                break;
            default:
                if (version_compare(PHP_VERSION, '5.1.3') < 0) {
                    throw new Exception\InvalidArgumentException('Too many arguments. Need a version of PHP to upper 5.1.3');
                }
                $ref = new ReflectionClass($className);
                $instance = $ref->newInstanceArgs($params);
                break;
        }
        if($instance instanceof ServiceLocatorAwareInterface)
            $instance->setServiceLocator($this);
        return $instance;
    }

    protected function newInstanceByFactory(ComponentDefinition $component,$componentName)
    {
        $factory = $component->getFactory();
        if(!is_callable($factory))
            throw new Exception\DomainException('invalid factory "'.(is_string($factory) ? $factory : 'function').'" in the component "'.($componentName ? $componentName : $component->getName()).'".');
        $factoryArgs = $component->getFactoryArgs();
        $instance = call_user_func($factory,$this,$componentName,$factoryArgs);
        if($instance instanceof ServiceLocatorAwareInterface)
            $instance->setServiceLocator($this);
        return $instance;
    }

    public function setInstance($componentName,$instance)
    {
        $this->instanceManager->setInstance($componentName,$instance);
        return $this;
    }
}
