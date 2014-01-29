<?php
namespace Aneris\Aop;

interface EventInterface
{
    public function setName($name);
    public function getName();
    public function setTarget($target);
    public function getTarget();
    public function setBreak($status);
    public function getBreak();
    public function getPreviousResult();
    public function setPreviousResult($previousResult);
    public function getArgs();
    public function getArg($name,$default=null);
    public function setArgs(array $args);
    public function setArg($name,$value);
}
