<?php
namespace Aneris\Mvc\RoutingTableBuilder;

use Aneris\Mvc\Annotations\Controller;
use Aneris\Mvc\Annotations\RequestMapping;
use Aneris\Mvc\RoutingTableBuilderInterface;
use Aneris\Annotation\NameSpaceExtractor;
use Aneris\Annotation\AnnotationManager;
use Aneris\Mvc\Exception;
use ReflectionClass;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Annotation implements RoutingTableBuilderInterface
{
    const DEFAULT_ANNOTATION_READER = 'Aneris\Annotation\AnnotationManager';
    protected $config;
    protected $annotationReader;
    protected $serviceLocator;
    protected $routes = array();

    public function __construct($serviceLocator=null)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function setConfig(array $config=null)
    {
        $this->config = $config;
    }

    public function setAnnotationReader($annotationReader)
    {
        $this->annotationReader = $annotationReader;
        return $this;
    }

    public function getAnnotationReader()
    {
        if($this->annotationReader)
            return $this->annotationReader;
        if($this->serviceLocator) {
            if(isset($this->config['annotation_reader'])) {
                $readerName = $this->config['annotation_reader'];
                $this->annotationReader = $this->serviceLocator->get($readerName);
            } else {
                $readerName = self::DEFAULT_ANNOTATION_READER;
                if($this->serviceLocator->has($readerName))
                    $this->annotationReader = $this->serviceLocator->get($readerName);
                else
                    $this->annotationReader = $readerName::factory();
            }
        } else {
            $this->annotationReader = AnnotationManager::factory();
        }
        return $this->annotationReader;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function build(array $paths=null)
    {
        if($paths==null) {
            if(!isset($this->config['controller_paths']))
                throw new Exception\DomainException('a path of annotaion driven controllers is not specified.');
            $paths = $this->config['controller_paths'];
        }

        foreach ($paths as $path => $switch) {
            if($switch)
                $this->scanDirectory($path);
        }
        return $this;
    }

    protected function scanDirectory($path)
    {
        if(!file_exists($path)) {
            throw new Exception\DomainException('annotation driven mvc controller directory not found: '.$path);
        }
        $fileSPLObjects = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );
        foreach($fileSPLObjects as $fullFileName => $fileSPLObject) {
            $filename = $fileSPLObject->getFilename();
            if (!is_dir($fullFileName)) {
                if($filename!='.' && $filename!='..') {
                    $this->parseControllerFile($fullFileName);
                }
            }
        }
        return $this;
    }

    public function parseControllerFile($filename)
    {
        require_once $filename;
        $parser = new NameSpaceExtractor($filename);
        $classes = $parser->getAllClass();
        if($classes==null)
            return $this;
        foreach($classes as $class) {
            $this->parseControllerClass($class);
        }
        return $this;
    }

    public function parseControllerClass($class)
    {
        $isController = false;
        $classRef = new ReflectionClass($class);
        $annos = $this->getAnnotationReader()->getClassAnnotations($classRef);
        foreach ($annos as $anno) {
            if($anno instanceof Controller) {
                $isController = true;
            } else if($anno instanceof RequestMapping) {
                $controllers[$class]['RequestMapping'] = $anno;
            }
        }
        if(!$isController)
            return $this;

        $controllers[$class]['ClassName'] = $class;
        $this->extractRoutes($classRef,$controllers[$class]);
        return $this;
    }

    protected function extractRoutes($classRef,$controller)
    {
        $count = 0;
        $annotationManager = $this->getAnnotationReader();
        foreach($classRef->getMethods() as $ref) {
            $annos = $annotationManager->getMethodAnnotations($ref);
            foreach($annos as $anno) {
                if($anno instanceof RequestMapping) {
                    $route = array();
                    $path = '';
                    if(isset($controller['RequestMapping']))
                        $path .= rtrim($controller['RequestMapping']->value,'/');
                    if(!isset($anno->value)) {
                        $filename = $ref->getFileName();
                        $lineNumber = $ref->getStartLine();
                        throw new Exception\DomainException('a mapping path is not specified.: '.$filename.'('.$lineNumber.')');
                    }
                    $path = rtrim($path . '/' . trim($anno->value,'/'),'/');
                    if($path=='')
                        $path='/';
                    $route['path'] = $path;
                    if(isset($anno->method))
                        $route['conditions']['method'] = strtoupper($anno->method);
                    if(isset($anno->headers))
                        $route['conditions']['headers'] = $anno->headers;

                    if($anno->ns)
                        $route['defaults']['namespace'] = $anno->ns;
                    else if(isset($controller['RequestMapping']->ns))
                        $route['defaults']['namespace'] = $controller['RequestMapping']->ns;
                    else
                        $route['defaults']['namespace'] = $classRef->getNamespaceName();

                    $route['defaults']['class'] = $controller['ClassName'];
                    $route['defaults']['method'] = $ref->name;
                    if($anno->view)
                        $route['defaults']['view'] = $anno->view;
                    if(isset($anno->parameters)) {
                        $route['type'] = 'segment';
                        $route['options']['parameters'] = $anno->parameters;
                    } else {
                        $route['type'] = 'literal';
                    }

                    if($anno->name)
                        $routeName = $route['defaults']['namespace'].'\\'.$anno->name;
                    else
                        $routeName = $controller['ClassName'].'::'.$ref->name;
                    if(isset($this->routes[$routeName]))
                        throw new Exception\DomainException('duplicate route name "'.$routeName.'": '.$ref->getFileName().'('.$ref->getStartLine().')');
                    $this->routes[$routeName] = $route;
                    $count++;
                }
            }
        }
        if($count==0) {
            $filename = $classRef->getFileName();
            $lineNumber = $classRef->getStartLine();
            throw new Exception\DomainException('there is no action-method in a controller.: '.$filename.'('.$lineNumber.')');
        }
        return $this;
    }

    public function compileRoute($routeStr)
    {

    }
}

