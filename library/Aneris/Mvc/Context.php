<?php
namespace Aneris\Mvc;

use Aneris\Http\HttpRequestInterface;
use Aneris\Http\HttpResponseInterface;
use Aneris\Container\ServiceLocatorInterface;

class Context
{
    protected $httpRequest;
    protected $httpResponse;
    protected $params;
    protected $serviceManager;
    protected $pluginManager;
    protected $router;
    protected $attributes;
    protected $routeInformation;
    protected $view;

    public function __construct(
        HttpRequestInterface $httpRequest=null,
        HttpResponseInterface $httpResponse=null,
        array $params=null,
        ServiceLocatorInterface $serviceManager=null,
        PluginManager $pluginManager=null)
    {
        $this->setRequest($httpRequest);
        $this->setHttpResponse($httpResponse);
        $this->setParams($params);
        $this->serviceManager = $serviceManager;
        $this->pluginManager = $pluginManager;
    }

    public function getServiceLocator()
    {
        return $this->serviceManager;
    }

    public function setRequest(HttpRequestInterface $httpRequest=null)
    {
        $this->httpRequest = $httpRequest;
    }

    public function getRequest()
    {
        return $this->httpRequest;
    }

    public function setHttpResponse(HttpResponseInterface $httpResponse=null)
    {
        $this->httpResponse = $httpResponse;
    }

    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    public function setParam($name,$value)
    {
        $this->params[$name] = $value;
    }

    public function getParam($name,$default=null)
    {
        return (isset($this->params[$name])) ? $this->params[$name] : $default;
    }

    public function setParams(array $params=null)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setAttribute($name,$value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function getAttribute($name,$default=null)
    {
        if(isset($this->attributes[$name]))
            $value = $this->attributes[$name];
        else
            $value = $default;
        return $value;
    }

    public function setAttributes(array $attributes)
    {
        return $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }

    public function getView()
    {
        return $this->view;
    }

    public function getNamespace()
    {
        if(isset($this->params['namespace']))
            return $this->params['namespace'];
        return null;
    }

    public function setRouteInformation($name,$value)
    {
        $this->routeInformation[$name] = $value;
        return $this;
    }

    public function getRouteInformation($name)
    {
        if(isset($this->routeInformation[$name]))
            return $this->routeInformation[$name];
        else
            return null;
    }

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getPluginManager()
    {
        return $this->pluginManager;
    }

    public function __call($method,$params)
    {
        if($this->pluginManager==null)
            throw new Exception\DomainException('pluginManager is not specified.');
        $plugin = $this->pluginManager->get($method);
        if (is_callable($plugin)) {
            return call_user_func_array($plugin, $params);
        }

        return $plugin;
    }
}