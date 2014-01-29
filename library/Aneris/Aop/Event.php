<?php
namespace Aneris\Aop;

class Event implements EventInterface
{
    protected $name;
    protected $args;
    protected $target;
    protected $previousResult;
    protected $breakStatus;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        if(is_array($this->name)) {
            reset($this->name);
            return current($this->name);
        }
        return $this->name;
    }
    
    public function getNames()
    {
        return $this->name;
    }

    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setBreak($status)
    {
        $this->breakStatus = $status;
        return $this;
    }

    public function getBreak()
    {
        return $this->breakStatus;
    }

    public function getPreviousResult()
    {
        return $this->previousResult;
    }

    public function setPreviousResult($previousResult)
    {
        $this->previousResult = $previousResult;
        return $this;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getArg($name,$default=null)
    {
    	if(is_array($this->args) && array_key_exists($name,$this->args))
            return $this->args[$name];
        return $default;
    }

    public function setArgs(array $args)
    {
        $this->args = $args;
        return $this;
    }

    public function setArg($name,$value)
    {
        $this->args[$name] = $value;
        return $this;
    }
}