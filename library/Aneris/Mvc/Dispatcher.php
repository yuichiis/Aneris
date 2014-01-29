<?php
namespace Aneris\Mvc;

use Aneris\Container\ServiceLocatorInterface;

class Dispatcher
{
    protected $config;
    protected $serviceLocator;
    protected $diContainer;

    public function __construct($serviceLocator=null)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function dispatch(Context $context)
    {
        $className  = $this->getControllerName($context);
        $methodName = $this->getMethodName($context);

        $controller = $this->getController($className);

        if(!is_callable(array($controller,$methodName)))
            throw new Exception\DomainException('A action method is not found in a controller.(Class:'.$className.',Method:'.$methodName);

        $context->setRouteInformation('method',$className.'::'.$methodName.'()');
        return $controller->$methodName($context);
    }

    protected function getControllerName(Context $context)
    {
        $param = $context->getParams();
        if(isset($param['class']))
            return $param['class'];

        if(!isset($param['controller']))
            throw new Exception\DomainException('A controller parameter is not specified from a route.');

        if(isset($param['namespace'])) {
            if(isset($this->config['invokables'])) {
                $controllerName = $param['namespace'].'\\'.$param['controller'];
            } else {
                $controllerName = $param['namespace'].'\\'.ucfirst(strtolower($param['controller']));
            }
        } else {
            $controllerName = $param['controller'];
        }
        if(isset($this->config['invokables'])) {
            if(!isset($this->config['invokables'][$controllerName]))
                throw new Exception\PageNotFoundException('A controller is not found in the invokables configuration:'.$controllerName);
            $className = $this->config['invokables'][$controllerName];
        } else {
            $className = $controllerName.'Controller';
        }
        return $className;
    }

    protected function getMethodName(Context $context)
    {
        $param = $context->getParams();
        if(isset($param['method']))
            return $param['method'];

        if(!isset($param['action']))
            throw new Exception\DomainException('A action parameter is not specified from a route.');
        return $param['action'].'Action';
    }

    protected function getController($className)
    {
        if($this->serviceLocator) {
            $controller = $this->serviceLocator->get($className);
        } else {
            if(!class_exists($className)) {
                throw new Exception\PageNotFoundException('A controller is not found:'.$className);
            }
            $controller = new $className();
        }
        return $controller;
    }
}
