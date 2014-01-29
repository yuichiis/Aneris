<?php
namespace Aneris\Event;

use Aneris\Event\Exception;

class Listener
{
    protected $callback;
    protected $priority;

    public function __construct($callback=null,$priority=1)
    {
        $this->callback = $callback;
        $this->priority = $priority;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function update(Event $event)
    {
        if(!is_callable($this->callback))
            throw new Exception\DomainException('listener is not callable at event('.$event->getName().')');
        return call_user_func($this->callback, $event);
    }
}
