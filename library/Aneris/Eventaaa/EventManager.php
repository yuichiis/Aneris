<?php
namespace Aneris\Event;

use Aneris\Event\Exception;

class EventManager implements EventManagerInterface
{
    protected $events = array();
    protected $doOverlookNotDefined = false;
    public $lastExceptionEvent;

    public function setOverlookNotDefined($flag=true)
    {
        $this->doOverlookNotDefined = $flag;
    }

    public function attach($eventName, $callback = null, $priority = 1)
    {
        if(!isset($this->events[$eventName])) {
            $this->events[$eventName] = new Event($eventName);
        }

        if(!is_callable($callback))
            throw new Exception\DomainException('a entry is not callable in event('.$eventName.')');

        $listener = new Listener($callback,$priority);
        $this->events[$eventName]->attach($listener);
    }

    public function notify($eventName, array $args = array(), $target = null, $previousResult=null)
    {
        if(!isset($this->events[$eventName])) {
            if($this->doOverlookNotDefined) {
                return null;
            }
            throw new Exception\DomainException('the event is not defined('.$eventName.')');
        }

        $this->events[$eventName]->setTarget($target);
        $res = $this->events[$eventName]->notify($args, $previousResult);
        $this->events[$eventName]->setTarget(null);
        return $res;
    }
}
