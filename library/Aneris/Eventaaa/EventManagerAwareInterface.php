<?php
namespace Aneris\Event;

interface EventManagerAwareInterface
{
    public function setEventManager(EventManagerInterface $events);
}
