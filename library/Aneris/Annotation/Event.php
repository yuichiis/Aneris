<?php
namespace Aneris\Annotation;

class Event
{
    protected $name;
    protected $args;

    public function __construct($name=null,$args=null)
    {
        $this->name = $name;
        $this->args = $args;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getArgs()
    {
        return $this->args;
    }
}
