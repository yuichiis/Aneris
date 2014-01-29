<?php
namespace Aneris\Aop;

use Aneris\Container\Container;
use Aneris\Container\ComponentDefinition;
use Aneris\Container\Definition;

class Interceptor
{
    protected $__aop_serviceContainer;
    protected $__aop_component;
    protected $__aop_events;
    protected $__aop_instance;
    protected $__aop_constructor;
    protected $__aop_className;
    protected $__aop_is_initialized;

    public function __construct(
        Container $container, 
        ComponentDefinition $component,
        EventManagerInterface $events=null,
        $lazy=null,
        $instance=null,
        $constructor=null)
    {
        $this->__aop_serviceContainer = $container;
        $this->__aop_component = $component;
        $this->__aop_events = $events;
        $this->__aop_instance = $instance;
        $this->__aop_constructor = $constructor;
        $this->__aop_className = $this->__aop_component->getClassName();
        if(!$lazy) {
            $this->__aop_instantiate();
        }
    }

    public function __aop_instantiate()
    {
        if($this->__aop_is_initialized)
            return;
        $this->__aop_is_initialized = true; // CAUTION: this sentence must be here for anti infinity loop.
        $instance = $this->__aop_serviceContainer->instantiate($this->__aop_component,null,null,$this->__aop_instance,$this->__aop_constructor);
        if($this->__aop_instance===null)
            $this->__aop_instance = $instance;
    }

    public function __aop_before($methodName, array $arguments)
    {
        $this->__aop_instantiate();
        if($this->__aop_events) {
            $args['arguments'] = $arguments;
            $event = new AopEvent();
            $event->setName(array(
                'before::execution('.$this->__aop_className.'::'.$methodName.'())',
                'before::execution('.$this->__aop_className.'::*)',
                'before::execution(*::'.$methodName.'())',
                'before::execution(*::*)',
            ));
            $this->__aop_events->notify($event,$args,$this->__aop_instance);
        }
    }

    public function __aop_afterThrowing($methodName, array $arguments, \Exception $e)
    {
        if($this->__aop_events) {
            //$this->__aop_events->lastExceptionEvent = spl_object_hash($e);
            $args['arguments'] = $arguments;
            $args['throwing'] = $e;
            $event = new AopEvent();
            $event->setName(array(
                'after-throwing::execution('.$this->__aop_className.'::'.$methodName.'()::'.get_class($e).')',
                'after-throwing::execution('.$this->__aop_className.'::'.$methodName.'()::*)',
                'after-throwing::execution('.$this->__aop_className.'::*::'.get_class($e).')',
                'after-throwing::execution('.$this->__aop_className.'::*::*)',
                'after-throwing::execution(*::'.$methodName.'()::'.get_class($e).')',
                'after-throwing::execution(*::'.$methodName.'()::*)',
                'after-throwing::execution(*::*::'.get_class($e).')',
                'after-throwing::execution(*::*::*)',
                'after::execution('.$this->__aop_className.'::'.$methodName.'())',
                'after::execution('.$this->__aop_className.'::*)',
                'after::execution(*::'.$methodName.'())',
                'after::execution(*::*)',
            ));
            $this->__aop_events->notify($event,$args,$this->__aop_instance);
        }
    }

    public function __aop_afterReturning($methodName, array $arguments, $result)
    {
        if($this->__aop_events) {
            $args['arguments'] = $arguments;
            $args['returning'] = $result;
            $event = new AopEvent();
            $event->setName(array(
                'after-returning::execution('.$this->__aop_className.'::'.$methodName.'())',
                'after-returning::execution('.$this->__aop_className.'::*)',
                'after-returning::execution(*::'.$methodName.'())',
                'after-returning::execution(*::*)',
                'after::execution('.$this->__aop_className.'::'.$methodName.'())',
                'after::execution('.$this->__aop_className.'::*)',
                'after::execution(*::'.$methodName.'())',
                'after::execution(*::*)',
            ));
            $this->__aop_events->notify($event,$args,$this->__aop_instance);
        }
    }

    protected function __aop_merge_queue($eventQueue,$pointcut)
    {
        $queue = $this->__aop_events->fetch($pointcut);
        if($queue)
            $eventQueue->merge($queue);
    }

    public function __aop_around($methodName, array $arguments,$instanceMethod=null)
    {
        if($instanceMethod===null)
            $instanceMethod = $methodName;
        $callback = array($this->__aop_instance,$instanceMethod);
        if($this->__aop_events) {
            $event = new AopEvent();
            $event->setName(array(
                'around::execution('.$this->__aop_className.'::'.$methodName.'())',
                'around::execution('.$this->__aop_className.'::*)',
                'around::execution(*::'.$methodName.'())',
                'around::execution(*::*)',
            ));
            return $this->__aop_events->call($event,null,$this->__aop_instance,$callback,$arguments);
        } else {
            return call_user_func_array($callback, $arguments);
        }
    }

    public function __call($methodName, array $arguments)
    {
        $this->__aop_before($methodName, $arguments);
        try {
            $result = $this->__aop_around($methodName,$arguments);
        } catch(\Exception $e) {
            $this->__aop_afterThrowing($methodName, $arguments, $e);
            throw $e;
        }
        $this->__aop_afterReturning($methodName, $arguments, $result);
        return $result;
    }

    public function __get($varName)
    {
        $this->__aop_instantiate();
        if($this->__aop_events) {
            $args['name'] = $varName;
            $event = new AopEvent();
            $event->setName(array(
                'before::get('.$this->__aop_className.'::'.$varName.')',
                'before::get('.$this->__aop_className.'::*)',
                'before::get(*::'.$varName.')',
                'before::get(*::*)',
            ));
            $this->__aop_events->notify($event,$args,$this->__aop_instance);
        }
        $value = $this->__aop_instance->$varName;
        if($this->__aop_events) {
            $args['value'] = $value;
            $event = new AopEvent();
            $event->setName(array(
                'after::get('.$this->__aop_className.'::'.$varName.')',
                'after::get('.$this->__aop_className.'::*)',
                'after::get(*::'.$varName.')',
                'after::get(*::*)',
            ));
            $this->__aop_events->notify($event,$args,$this->__aop_instance);
        }
        return $value;
    }

    public function __set($varName, $value)
    {
        $this->__aop_instantiate();
        if($this->__aop_events) {
            $args['name'] = $varName;
            $args['value'] = $value;
            $event = new AopEvent();
            $event->setName(array(
                'before::set('.$this->__aop_className.'::'.$varName.')',
                'before::set('.$this->__aop_className.'::*)',
                'before::set(*::'.$varName.')',
                'before::set(*::*)',
            ));
            $this->__aop_events->notify($event,$args,$this->__aop_instance);
        }
        $this->__aop_instance->$varName = $value;
        if($this->__aop_events) {
            $event = new AopEvent();
            $event->setName(array(
                'after::set('.$this->__aop_className.'::'.$varName.')',
                'after::set('.$this->__aop_className.'::*)',
                'after::set(*::'.$varName.')',
                'after::set(*::*)',
            ));
            $this->__aop_events->notify($event,$args,$this->__aop_instance);
        }
    }

    public function __isset($varName)
    {
        $this->__aop_instantiate();
        return isset($this->__aop_instance->$varName);
    }

    public function __unset($varName)
    {
        $this->__aop_instantiate();
        unset($this->__aop_instance->$varName);
    }

    public static function __callStatic($methodName, array $arguments)
    {
        throw new Exception\DomainException('static method is not supported to call a interceptor in "'.get_called_class().'".');
    }
}
