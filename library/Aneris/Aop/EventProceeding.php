<?php
namespace Aneris\Aop;

use Iterator;
use Aneris\Container\ServiceLocatorInterface;

class EventProceeding
{
    protected $event;
    protected $terminator;
    protected $arguments;
    protected $iterator;
    protected $serviceLocator;

    public function __construct(
        EventInterface $event,
        $terminator,
        array $arguments,
        Iterator $iterator,
        ServiceLocatorInterface $serviceLocator=null)
    {
        $this->event = $event;
        $this->terminator = $terminator;
        $this->arguments = $arguments;
        $this->iterator = $iterator;
        $this->serviceLocator = $serviceLocator;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function proceed(array $arguments=null)
    {
        if($arguments===null)
            $arguments = $this->arguments;
        else
            $this->arguments = $arguments;
        if(!$this->iterator->valid()) {
            if($this->terminator==null) {
                if(isset($arguments[0]))
                    return $arguments[0];
                else
                    return null;
            }
            if(!is_callable($this->terminator))
                throw new Exception\DomainException('invalid terminator callback on event "'.$this->event->getName().'"' );
            if($arguments==null)
                $arguments=array();
            return call_user_func_array($this->terminator, $arguments);
        }
        $listener = $this->iterator->current();
        $current = $listener->getCallBack();
        if($current==null) {
            if($this->serviceLocator==null)
                throw new Exception\DomainException('it need a service locator to instantiate callback if you want to set null to callback in the event "'.$this->getName().'".');
            $current = array(
                $this->serviceLocator->get($listener->getClassName()),
                $listener->getMethodName(),
            );
            if(!is_callable($current))
                throw new Exception\DomainException('invalid listener.: '.$listener->getClassName().'::'.$listener->getMethodName());
            $listener->setCallback($current);
        }
        $this->iterator->next();
        $result = call_user_func($current,$this,$arguments);
        return $result;
    }
}
