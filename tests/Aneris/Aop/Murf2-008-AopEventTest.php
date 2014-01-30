<?php
namespace AnerisTest\EventTest;

// Test Target Classes
use Aneris\Aop\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testEvent()
    {
        $event = new Event();
        $event->setName('test');
        $this->assertEquals('test',$event->getName());
        $event->setTarget('target');
        $this->assertEquals('target',$event->getTarget());
        $event->setBreak('break');
        $this->assertEquals('break',$event->getBreak());
        $event->setPreviousResult('result');
        $this->assertEquals('result',$event->getPreviousResult());
        $event->setArgs(array('ag1'=>'val1'));
        $this->assertEquals(array('ag1'=>'val1'),$event->getArgs());
        $this->assertEquals('val1',$event->getArg('ag1'));
        $this->assertEquals('default',$event->getArg('agx','default'));
        $event->setArg('ag2','val2');
        $this->assertEquals(array('ag1'=>'val1','ag2'=>'val2'),$event->getArgs());

        $event = new Event();
        $this->assertEquals('default',$event->getArg('agx','default'));
        $event->setArg('ag2',null);
        $this->assertEquals(null,$event->getArg('ag2','default'));
    }
}
