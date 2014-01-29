<?php
namespace Aneris\Aop;

use ReflectionClass;
use Aneris\Container\Container;
use Aneris\Container\ProxyManagerInterface;
use Aneris\Container\ComponentScanner;
use Aneris\Container\ComponentDefinition;
use Aneris\Container\Definition;
use Aneris\Container\ServiceLocatorInterface;
use Aneris\Stdlib\Cache\CacheFactory;
use ArrayObject;
use Aneris\Aop\Annotations\Before;
use Aneris\Aop\Annotations\AfterReturning;
use Aneris\Aop\Annotations\AfterThrowing;
use Aneris\Aop\Annotations\Around;

class AopManager implements ProxyManagerInterface
{
    const ANNOTATION_ASPECT = 'Aneris\Aop\Annotations\Aspect';
    const ANNOTATION_PROXY  = 'Aneris\Container\Annotations\Proxy';
    const CACHE_EVENTMANAGER = 'EventManager';
    const CACHE_ASPECTCLASSNAME = 'aspectClassName';
    const CACHE_JOINPOINT   = 'joinPoint';
    const CACHE_INITIALIZED = '__INITIALIZED__';


    static private $adviceTypes = array(
        AspectInterface::ADVICE_BEFORE => true,
        AspectInterface::ADVICE_AFTER_RETURNING => true,
        AspectInterface::ADVICE_AFTER_THROWING => true,
        AspectInterface::ADVICE_AFTER => true,
        AspectInterface::ADVICE_AROUND => true,
    );
    static private $joinPointTypes = array(
        'execution' => true,
        'set' => true,
        'get' => true,
        'label' => true,
    );

    protected $eventManager;
    protected $aspects = array();
    protected $aspectCache;
    protected $enableCache = true;
    protected $cachePath;
    protected $annotationManager;
    protected $container;
    protected $aspectClassName;
    protected $config;
    protected $joinPoint;

    public function __construct(Container $container,EventManager $eventManager=null,InterceptorBuilder $interceptorBuilder=null)
    {
        $this->container = $container;
        $this->getAspectCache();
        if(isset($this->aspectCache[self::CACHE_EVENTMANAGER]))
            $this->eventManager = $this->aspectCache[self::CACHE_EVENTMANAGER];
        else if($eventManager)
            $this->eventManager = $eventManager;
        else
            $this->eventManager = new EventManager();
        if(isset($this->aspectCache[self::CACHE_ASPECTCLASSNAME]))
            $this->aspectClassName = $this->aspectCache[self::CACHE_ASPECTCLASSNAME];

        $this->eventManager->setServiceLocator($container);
        $this->annotationManager = $container->getAnnotationManager();
        if($interceptorBuilder)
            $this->interceptorBuilder = $interceptorBuilder;
        else
            $this->interceptorBuilder = new InterceptorBuilder();
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function setConfig(array $config=null)
    {
        if($config==null)
            return;
        $this->config = $config;
        if(isset($config['aspects']) && !$this->hasScanned()) {
            foreach($config['aspects'] as $className  => $advices) {
                $this->addAspect($className,$advices);
            }
        }
    }

    public function attachScanner(ComponentScanner $componentScanner)
    {
        $componentScanner->attachCollect(
            self::ANNOTATION_ASPECT,
            array($this,'collectAspect'));
        //$componentScanner->attachCollect(
        //    self::ANNOTATION_PROXY,
        //    array($this,'collectJoinPoint'));
        $componentScanner->attachCompleted(
            self::ANNOTATION_ASPECT,
            array($this,'completedScan'));
    }

    public function newProxy(
        Container $container,
        ComponentDefinition $component,
        array $options=null)
    {
        $mode = isset($options['mode']) ? $options['mode'] : null;
        $lazy = isset($options['lazy']) ? $options['lazy'] : null;
        $className = $component->getClassName();
        if(isset($this->aspectClassName[$className]) || $mode==='disable') {
            return $container->instantiate($component,$className);
        }
        if($component->hasFactory()) {
            if($className=='array')
                return $container->instantiate($component,$className);
            $mode='interface';
        }
        if(!class_exists($className))
            throw new Exception\DomainException('class name is not specifed for interceptor in component "'.$component->getName().'".');
                
        $fileName = $this->interceptorBuilder->getInterceptorFileName($className,$mode);
        if(!file_exists($fileName))
            $this->interceptorBuilder->buildInterceptor($className,$mode);
        require_once $fileName;
        $interceptorName = $this->interceptorBuilder->getInterceptorClassName($className,$mode);

        if(isset($this->config['disable_interceptor_event']))
            $eventManager = null;
        else
            $eventManager = $this->eventManager;
        return new $interceptorName(
            $container,
            $component,
            $eventManager,
            $lazy);
    }

    public function notify(
        $label,
        array $args=null,
        $target=null,
        $previousResult=null,
        $className=null,
        $methodName=null)
    {
        $names = array('before::label(*::*::'.$label.')');
        if($methodName)
            $names[] = 'before::label(*::'.$methodName.'()::'.$label.')';
        if($className)
            $names[] = 'before::label('.$className.'::*::'.$label.')';
        if($className && $methodName)
            $names[] = 'before::label('.$className.'::'.$methodName.'()::'.$label.')';
        $event = new AopEvent();
        $event->setName($names);
        return $this->eventManager->notify($event, $args, $target, $previousResult);
    }

    public function call(
        $label,
        array $args=null,
        $target=null,
        $terminator=null,
        array $arguments=null,
        $className=null,
        $methodName=null)
    {
        $names = array('around::label(*::*::'.$label.')');
        if($methodName)
            $names[] = 'around::label(*::'.$methodName.'()::'.$label.')';
        if($className)
            $names[] = 'around::label('.$className.'::*::'.$label.')';
        if($className && $methodName)
            $names[] = 'around::label('.$className.'::'.$methodName.'()::'.$label.')';
        $event = new AopEvent();
        $event->setName($names);
        return $this->eventManager->call($event, $args, $target, $terminator, $arguments);
    }

    public function getAspectCache()
    {
        if($this->aspectCache)
            return $this->aspectCache;

        if(!$this->enableCache) {
            return $this->aspectCache = new ArrayObject();
        }
        if($this->cachePath)
            $path = $this->cachePath;
        else
            $path = '/'.__CLASS__;

        $this->aspectCache = CacheFactory::getInstance($path.'/aspects',true);
        return $this->aspectCache;
    }

    public function hasScanned()
    {
        return isset($this->aspectCache[self::CACHE_INITIALIZED]);
    }

    public function completedScan()
    {
        $this->eventManager->setServiceLocator(null);
        $this->aspectCache[self::CACHE_EVENTMANAGER] = $this->eventManager;
        $this->eventManager->setServiceLocator($this->container);
        $this->aspectCache[self::CACHE_ASPECTCLASSNAME] = $this->aspectClassName;
        $this->aspectCache[self::CACHE_JOINPOINT] = $this->joinPoint;

        $this->aspectCache[self::CACHE_INITIALIZED] = true;
    }

    public function addAspect($className,$advices=true)
    {
        if(!is_string($className))
            throw new Exception\InvalidArgumentException('must be class name.');
        if(is_array($advices)) {
            $found = $this->attachEvent($className,$advices);
        } else {
            $classRef = new ReflectionClass($className);
            $found = $this->collectAspect(null,$className,null,$classRef);
        }
        if(!$found)
            throw new Exception\DomainException('advice is not found in a class.: '.$className);
    }

    protected function attachEvent($className,$advices)
    {
        $found = false;
        foreach($advices as $methodName => $pointcuts) {
            if(!is_array($pointcuts))
                $pointcuts = array($pointcuts);
            foreach ($pointcuts as $pointcut) {
                $error=$this->checkPointcutSyntax($pointcut);
                if($error)
                    throw new Exception\DomainException($error.': in a aspect advice definition of '.$className.'::'.$methodName);
                $listener = new EventListener(null,$className,$methodName);
                $this->eventManager->attach(
                    $pointcut,
                    $listener
                );
                $found = true;
            }
        }
        return $found;
    }

    public function collectJoinPoint($annoName,$className,$anno,ReflectionClass $classRef)
    {
        if($this->annotationManager==null)
            return;
        foreach($classRef->getProperties() as $propertyRef) {
            $annos = $this->annotationManager->getPropertyAnnotations($propertyRef);
            foreach ($annos as $anno) {
                if($anno instanceof JoinPoint) {
                    $this->joinPoint[$className.'::$'.$propertyRef->name] = true;
                }
            }
        }
        foreach($classRef->getMethods() as $methodRef) {
            $annos = $this->annotationManager->getMethodAnnotations($methodRef);
            foreach ($annos as $anno) {
                if($anno instanceof JoinPoint) {
                    $this->joinPoint[$className.'::'.$methodRef->name.'()'] = true;
                }
            }
        }
    }
    public function collectAspect($annoName,$className,$anno,ReflectionClass $classRef)
    {
        $found = false;
        $this->aspectClassName[$className] = true;
        $getAdvicesFunction = $className.'::getAdvices';
        if(is_callable($getAdvicesFunction)) {
            $advices = call_user_func($getAdvicesFunction);
            if($this->attachEvent($className,$advices)) {
                $found = true;
            }
        }
        if($this->annotationManager==null)
            return $found;

        foreach($classRef->getMethods() as $methodRef) {
            $annos = $this->annotationManager->getMethodAnnotations($methodRef);
            foreach ($annos as $anno) {
                if($anno instanceof Before) {
                    $adviceType = AspectInterface::ADVICE_BEFORE;
                } else if($anno instanceof AfterReturning) {
                    $adviceType = AspectInterface::ADVICE_AFTER_RETURNING;
                } else if($anno instanceof AfterThrowing) {
                    $adviceType = AspectInterface::ADVICE_AFTER_THROWING;
                } else if($anno instanceof After) {
                    $adviceType = AspectInterface::ADVICE_AFTER;
                } else if($anno instanceof Around) {
                    $adviceType = AspectInterface::ADVICE_AROUND;
                } else {
                    $adviceType = null;
                }
                if($adviceType) {
                    $error=$this->checkPointcutSyntax($adviceType.'::'.$anno->value);
                    if($error)
                        throw new Exception\DomainException($error.': '.$methodRef->getFilename().'('.$methodRef->getStartLine().')');
                    $listener = new EventListener(null,$className,$methodRef->name);
                    $this->eventManager->attach(
                        $adviceType.'::'.$anno->value,
                        $listener
                    );
                    $found = true;
                }
            }
        }
        return $found;
    }

    public function checkPointcutSyntax($pointcut)
    {
        $t = preg_match('/^([a-z\\-]+)::([a-z\\-]+)\((.+)\)$/', $pointcut,$match);
        if(!$t || count($match)!=4)
            return 'invalid pointcut format.: "'.$pointcut.'"';
        list($all,$adviceType,$joinPointType,$path) = $match;
        if(!isset(self::$adviceTypes[$adviceType]))
            return 'unknown advice type.: "'.$adviceType.'".';
        if(!isset(self::$joinPointTypes[$joinPointType]))
            return 'unknown join point type.: "'.$joinPointType.'".';
        $parts = explode('::',$path);
        if(count($parts)<2 || count($parts)>3)
            return 'invalid path format.: "'.$path.'"';
        if($adviceType==AspectInterface::ADVICE_AFTER_THROWING ||
           $joinPointType=='label') {
            if(count($parts)!=3)
                return 'exception type or label name is not specified.: "'.$path.'"';
        } else {
            if(count($parts)!=2)
                return 'invalid path format.: "'.$path.'"';
        }
        if(!preg_match('/^(\\*|[a-zA-Z0-9_\\-\\\\]+)$/', $parts[0]))
            return 'invalid class name format.: "'.$parts[0].'"';
        if(!preg_match('/^(\\*|[a-zA-Z0-9_\\-]+\\(\\))$/', $parts[1]))
            return 'invalid method name format.: "'.$parts[1].'"';
        return null;
    }
}
