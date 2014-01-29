<?php
namespace Aneris\Mvc;

use Aneris\Http\HttpRequestInterface;
use Aneris\Mvc\Exception;
use Aneris\Mvc\Router\SegmentParser;
use Aneris\Mvc\Router\LiteralParser;
use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Container\ServiceLocatorInterface;

class Router
{
    protected static $builderAliases = array(
        'annotation' => 'Aneris\Mvc\RoutingTableBuilder\Annotation',
        'file'       => 'Aneris\Mvc\RoutingTableBuilder\File',
    );

    protected static $routerAliases = array(
        'segment' => 'Aneris\Mvc\Router\SegmentParser',
        'literal' => 'Aneris\Mvc\Router\LiteralParser',
    );

    protected $routingTable;
    protected $config;
    protected $cache;
    protected $cachePath;

    public function __construct($serviceLocator=null)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getCache()
    {
        if($this->cache)
            return $this->cache;

        if($this->cachePath)
            $path = $this->cachePath;
        else
            $path = '/'.__CLASS__;

        $this->cache = CacheFactory::getInstance($path);
        return $this->cache;
    }

    public function getRoutingTable()
    {
        if($this->routingTable)
            return $this->routingTable;
        $cache = $this->getCache();
        if(isset($cache['routes'])) {
            $this->routingTable = $cache['routes'];
            return $this->routingTable;
        }
        if($this->config==null)
            throw new Exception\DomainException('configuration is not found.');

        $routes = array();
        if(isset($this->config['builders'])) {
            foreach($this->config['builders'] as $builderClass => $builderConfig) {
                if(isset(self::$builderAliases[$builderClass]))
                    $builderClass = self::$builderAliases[$builderClass];
                if(!class_exists($builderClass))
                    throw new Exception\DomainException('routing table builder class is not found: '.$builderClass);
                $builder = new $builderClass($this->serviceLocator);
                if(!($builder instanceof RoutingTableBuilderInterface))
                    throw new Exception\DomainException('routing table builder class must implements "RoutingTableBuilderInterface": '.$builderClass);
                $builder->setConfig($builderConfig);
                $routes = array_merge($routes,$builder->build()->getRoutes());
            }
        }
        if(isset($this->config['routes']))
            $routes = array_merge($routes,$this->config['routes']);
        $this->routingTable = $routes;
        $cache['routes'] = $routes;
        return $this->routingTable;
    }

    public function match(Context $context)
    {
        $request = $context->getRequest();
        $path = $request->getPath();
        $route = $this->matchRoute($path, $request);
        if($route === false)
            return false;
        $context->setRouteInformation('route',$route['route_name']);
        $params = $this->parseParameter($path, $route);
        $params = $this->setDefaultParameter($params, $route);
        if(isset($params['namespace']))
            $params['namespace'] = str_replace(array('<','>','.','/','\'','"'),'',$params['namespace']);
        if(isset($params['controller']))
            $params['controller'] = str_replace(array('<','>','.','/','\\','\'','"','-','_'),'',$params['controller']);
        if(isset($params['action']))
            $params['action'] = str_replace(array('<','>','.','/','\\','\'','"','-','_'),'',$params['action']);
        $context->setParams($params);
        return $context;
    }

    public function parseParameter($path, $route)
    {
        $routerClass = $route['type'];
        if(isset(self::$routerAliases[$routerClass]))
            $routerClass = self::$routerAliases[$routerClass];
        if(!class_exists($routerClass))
            throw new Exception\DomainException('Unkown route type "'.$route['type'].'" in route "'.$route['route_name'].'"');
        return $routerClass::parse($path, $route);
    }

    public function matchRoute($path,$request)
    {
        $method  = $request->getMethod();
        $headers = $request->getHeaders();
        $name = null;
        $maxPath = '';
        foreach($this->getRoutingTable() as $idx => $info) {
            if(strpos($path, $info['path'])!==0)
                continue;
            if(isset($info['conditions']['method']) && $info['conditions']['method']!=$method)
                continue;
            if(isset($info['conditions']['headers']) && !$this->matchHeaders($info['conditions']['headers'],$headers))
                continue;
            if(strlen($maxPath) < strlen($info['path'])) {
                $maxPath = $info['path'];
                $name = $idx;
            }
        }

        if($name == null)
            return $this->getNotFound();

        $routingTable = $this->getRoutingTable();
        $route = $routingTable[$name];
        $route['route_name'] = $name;
        return $route;
    }

    public function matchHeaders($conditionHeaders,$headers)
    {
        $match = true;
        foreach($conditionHeaders as $key => $value) {
            if(!isset($headers[$key]) || strpos($headers[$key],$value)===false) {
                $match = false;
                break;
            }
        }
        return $match;
    }

    public function getNotFound()
    {
        throw new Exception\PageNotFoundException('A route is not found.');
    }

    public function setDefaultParameter($param, $route)
    {
        if(!isset($route['defaults']))
            return $param;
        return array_merge($route['defaults'], $param);
    }

    public function assemble(Context $context,$routeName,array $param=array(),$options=array())
    {
        $routingTable = $this->getRoutingTable();
        if(isset($options['namespace']))
            $routeName = $options['namespace'].'\\'.$routeName;
        else
            $routeName = $context->getNamespace().'\\'.$routeName;
        if(!isset($routingTable[$routeName]))
            throw new Exception\DomainException('route is not found:'.$routeName);
        $route = $routingTable[$routeName];

        $routerClass = $route['type'];
        if(isset(self::$routerAliases[$routerClass]))
            $routerClass = self::$routerAliases[$routerClass];
        if(!class_exists($routerClass))
            throw new Exception\DomainException('Unkown route type "'.$route['type'].'" in route "'.$routeName.'"');
        return $routerClass::assemble($param, $route);
    }
}
