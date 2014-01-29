<?php
namespace Aneris\Event;
interface EventManagerInterface
{
    public function attach($event_name, $callback = null, $priority = 1);
    public function notify($event_name, array $args = array(), $target = null, $previousResult=null);
}
