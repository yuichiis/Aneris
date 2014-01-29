<?php
namespace Aneris\Event;

use Aneris\Stdlib\PriorityQueue;

class Event
{
    protected $queue;
    protected $name;
    protected $breakStatus;
    protected $previousResult;
    protected $target;
    protected $args;

    public function __construct($name=null)
    {
        $this->name = $name;
    }

    protected function getQueue()
    {
        if(!isset($this->queue)) {
            $this->queue = new PriorityQueue();
        }
        return $this->queue;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setTarget($target)
    {
        return $this->target = $target;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setBreak($status)
    {
        $this->breakStatus = $status;
    }

    public function getPreviousResult()
    {
        return $this->previousResult;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function attach(Listener $listener)
    {
        $this->getQueue()->insert($listener,$listener->getPriority());
    }

    public function detach(Listener $listener)
    {
        $this->getQueue()->remove($listener);
    }

    public function notify(array $args = array(), $previousResult=null)
    {
        $this->args = $args;
        $queue = $this->getQueue();
        $this->previousResult = $previousResult;
        $this->breakStatus = false;
        foreach($queue as $listener) {
            $this->previousResult = $listener->update($this);
            if($this->breakStatus)
                break;
        }
        return $this->previousResult;
    }
}
