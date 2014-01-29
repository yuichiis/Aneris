<?php
namespace Aneris\Aop;

use Aneris\Stdlib\PriorityQueue;
use Aneris\Container\ServiceLocatorInterface;

class EventManager implements EventManagerInterface
{
    protected $serviceLocator;
	protected $queues=array();
    protected $queueClassName = 'Aneris\Stdlib\PriorityQueue';
    protected $priorityCapability = true;

    public function setQueueClassName($queueClassName,$priorityCapability)
    {
        $this->queueClassName = $queueClassName;
        $this->priorityCapability = $priorityCapability;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator=null)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function attach($eventName, $callback, $priority = 1)
    {
        if($callback instanceof EventListener) {
            $listener = $callback;
            $callback = $listener->getCallback();
            if($callback!=null && !is_callable($callback)) {
                throw new Exception\InvalidArgumentException('invalid callback in event "'.$eventName.'".');
            }
        } else {
            if(!is_callable($callback)) {
                throw new Exception\InvalidArgumentException('invalid callback in event "'.$eventName.'".');
            }
            $listener = new EventListener($callback);
        }
        $queueClass = $this->queueClassName;
    	if(!isset($this->queues[$eventName]))
            $this->queues[$eventName] = new $queueClass();
        if($this->priorityCapability)
            $this->queues[$eventName]->insert($listener,$priority);
        else
            $this->queues[$eventName][] = $listener;
    }

    public function select($event)
    {
        if(is_string($event)||is_array($event)) {
            $eventNames = $event;
            $event = new Event();
            $event->setName($eventNames);
        } else if($event instanceof EventInterface) {
            $eventNames = $event->getNames();
        } else {
            throw new Exception\InvalidArgumentException('invalid event type.');
        }
        if(!is_array($eventNames))
            $eventNames = array($eventNames);
        $eventQueue = new PriorityQueue();
        $found = null;
        foreach ($eventNames as $eventName) {
            if(!is_string($eventName))
                throw new Exception\InvalidArgumentException('invalid event name.');
            if(!isset($this->queues[$eventName]))
                continue;
            $eventQueue->merge($this->queues[$eventName]);
            $found = $event;
        }
        return array($found,$eventQueue);
    }

    public function notify(
        $event,
        array $args = null,
        $target = null,
        $previousResult=null)
    {
        list($event,$eventQueue) = $this->select($event);
        if($event==null)
            return $previousResult;
        if($args)
            $event->setArgs($args);
        if($target)
            $event->setTarget($target);
        if($previousResult)
            $event->setPreviousResult($previousResult);
        $event->setBreak(false);
        foreach ($eventQueue as $listener) {
            $callback = $listener->getCallback();
            if($callback==null) {
                if($this->serviceLocator==null)
                    throw new Exception\DomainException('it need a service locator to instantiate callback if you want to set null to callback in the event "'.$event->getName().'".');
                $callback = array(
                    $this->serviceLocator->get($listener->getClassName()),
                    $listener->getMethodName(),
                );
                if(!is_callable($callback))
                    throw new Exception\DomainException('invalid listener.: '.$listener->getClassName().'::'.$listener->getMethodName());
                $listener->setCallback($callback);
            }
            $previousResult = call_user_func($callback,$event);
            $event->setPreviousResult($previousResult);
            if($event->getBreak())
                break;
        }
        return $previousResult;
    }

    public function call(
        $event,
        array $args = null,
        $target = null,
        $terminator=null,
        array $arguments=null)
    {
        list($event,$eventQueue) = $this->select($event);
        if($arguments==null)
            $arguments = array();
        if($event==null) {
            if($terminator==null) {
                if(isset($arguments[0]))
                    return $arguments[0];
                else
                    return null;
            }
            if($arguments==null)
                $arguments=array();
            return call_user_func_array($terminator, $arguments);
        }
        if($args)
            $event->setArgs($args);
        if($target)
            $event->setTarget($target);
        $proceeding = new EventProceeding(
            $event,
            $terminator,
            $arguments,
            $eventQueue->getIterator(),
            $this->serviceLocator);
        return $proceeding->proceed();
    }

    public function fetch($eventName)
    {
        if(!isset($this->queues[$eventName]))
            return null;
        return $this->queues[$eventName];
    }

    public function getEventNames()
    {
        return array_keys($this->queues);
    }
}