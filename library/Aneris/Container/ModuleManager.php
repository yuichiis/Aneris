<?php
namespace Aneris\Container;

use Aneris\Stdlib\Cache\CacheFactory;

class ModuleManager
{
    protected $config;
    protected $mergedConfig;
    protected $cachedConfig;
    protected $modules;
    protected $serviceContainer;
    protected $diContainer;
    protected $initialized = false;
    protected $aopManager;

    public function __construct($config=null,$environment=null)
    {
        $this->config = $config;
        if(isset($config['cache']))
            CacheFactory::setConfig($config['cache']);
    }

    protected function loadModules()
    {
        if(isset($this->modules))
            return;

        if(!isset($this->config['module_manager']['modules']))
            throw new Exception\DomainException('Modules are not defined in module manager configuration.');

        $moduleNames = $this->config['module_manager']['modules'];
        if(!is_array($moduleNames)) {
            throw new Exception\InvalidArgumentException(
                'Argument must be set array. type is invalid:'.
                (is_object($moduleNames) ? get_class($moduleNames) : gettype($moduleNames))
            );
        }
        foreach($moduleNames as $className => $switch) {
            if(!$switch)
                continue;
            if(!class_exists($className))
                throw new Exception\DomainException('A class is not found:'.$className);
            $this->modules[$className] = new $className();
        }
    }

    public function getConfig()
    {
        if($this->mergedConfig)
            return $this->mergedConfig;

        $this->loadModules();
        $this->mergedConfig = array_replace_recursive(
            $this->getStaticConfig(),
            $this->getConfigClosure(),
            $this->config);
        return $this->mergedConfig;
    }

    protected function getStaticConfig()
    {
        if(isset($this->cachedConfig))
            return $this->cachedConfig;

        if(!isset($this->modules))
                throw new Exception\DomainException('Modules are not loaded.');

        $config = array();
        foreach($this->modules as $module) {
            if(method_exists($module,'getConfig'))
                $config = array_replace_recursive($config, $module->getConfig());
        }
        $this->cachedConfig = $config;
        return $this->cachedConfig;
    }

    protected function getConfigClosure()
    {
        $config = array();
        foreach($this->modules as $module) {
            if(method_exists($module,'getConfigClosure'))
                $config = array_replace_recursive($config, $module->getConfigClosure());
        }
        return $config;
    }

    //public function setServiceContainer($serviceContainer)
    //{
    //    $this->serviceContainer = $serviceContainer;
    //}

    protected function getServiceContainer()
    {
        if($this->serviceContainer)
            return $this->serviceContainer;

        $config = $this->getConfig();
        $this->serviceContainer = new Container();
        if(isset($config['container']))
            $this->serviceContainer->setConfig($config['container']);
        return $this->serviceContainer;
    }

    protected function initAopManager($serviceContainer)
    {
        if($this->aopManager)
            return $this->aopManager;
        $config = $this->getConfig();
        if(!isset($config['module_manager']['aop_manager']))
            return null;
        $className = $config['module_manager']['aop_manager'];
        if(!class_exists($className))
            throw new Exception\DomainException('Aop Manager not found:'.$className);
        if(isset($config['aop']))
            $config = $config['aop'];
        else
            $config = array();
        $this->aopManager = new $className($serviceContainer);
        $this->aopManager->setConfig($config);
        $serviceContainer->setProxyManager($this->aopManager);
        return $this->aopManager;
    }

    public function init()
    {
        if($this->initialized)
            return $this;

        $config = $this->getConfig();

        $serviceContainer = $this->getServiceContainer();
        $aopManager = $this->initAopManager($serviceContainer);
        $serviceContainer->scanComponents();

        $serviceContainer->setInstance(get_class($this),$this);
        $componentManager = $serviceContainer->getComponentManager();
        $componentManager->addAlias('ModuleManager',get_class($this));
        $serviceContainer->setInstance(get_class($serviceContainer),$serviceContainer);
        $componentManager->addAlias('ServiceLocator',get_class($serviceContainer));
        if($aopManager) {
            $serviceContainer->setInstance(get_class($aopManager),$aopManager);
            $componentManager->addAlias('AopManager',get_class($aopManager));
        }

        $serviceContainer->setInstance('config',$config);
/*
        $diContainer = $this->getDiContainer();
        if($diContainer) {
            $serviceContainer->setService(get_class($diContainer),$diContainer);
            $componentManager->addAlias('Di',get_class($diContainer));
            $componentManager->addAlias('DependencyInjection',get_class($diContainer));
        }
*/
        foreach($this->modules as $module) {
            if(method_exists($module,'init'))
                $module->init($this);
        }
        $this->initialized = true;
        return $this;
    }

    public function getServiceLocator()
    {
        $this->init();
        return $this->getServiceContainer();
    }

    public function run($moduleName=null)
    {
        $this->init();
        $config = $this->getConfig();
        if($moduleName==null && isset($config['module_manager']['autorun']))
            $moduleName = $config['module_manager']['autorun'];
        if(!isset($this->modules[$moduleName]))
            throw new Exception\DomainException('The Module is not defined:'.$moduleName);

        $instance = $this->modules[$moduleName];
        $method = 'invoke';
        $class = 'self';

        if(isset($config['module_manager']['invokables'][$moduleName])) {
            $config = $config['module_manager']['invokables'][$moduleName];
            if(is_string($config)) {
                $class = $config;
            } else if(is_array($config)) {
                if(isset($config['class']))
                    $class = $config['class'];
                if(isset($config['method']))
                    $method = $config['method'];
            } else {
                throw new Exception\DomainException('A invokable configuration must be string or array.:'.$moduleName);
            }
        }

        if($class != 'self') {
            $instance = $this->serviceContainer->get($class);
        }

        if(!method_exists($instance,$method))
            throw new Exception\DomainException('The Module do not have invokable method for invokables configuration:'.$moduleName.'::'.$method);

        if(isset($config['config_injector'])) {
            print_r($config);
            $module_config = $this->serviceContainer->get('config');
            $config_injector = $config['config_injector'];
            $instance->$config_injector($module_config);
        }
        
        return $instance->$method($this);
    }
}
