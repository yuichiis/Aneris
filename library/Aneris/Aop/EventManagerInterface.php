<?php
namespace Aneris\Aop;

interface EventManagerInterface
{
    public function attach($eventName, $callback, $priority = 1);
    public function notify($event, array $args = null, $target = null, $previousResult=null);
}
