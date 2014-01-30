<?php
namespace AnerisTest\EventListenerTest;

// Test Target Classes
use Aneris\Aop\EventListener;

class EventListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testEventListener()
    {
        $event = new EventListener('callback','classname','methodname');
        $this->assertEquals('callback',$event->getCallBack());
        $this->assertEquals('classname',$event->getClassName());
        $this->assertEquals('methodname',$event->getMethodName());

        $event = new EventListener();
        $this->assertEquals(null,$event->getCallBack());
        $this->assertEquals(null,$event->getClassName());
        $this->assertEquals(null,$event->getMethodName());
        $event->setCallBack('foofunc');
        $this->assertEquals('foofunc',$event->getCallBack());
    }
}
