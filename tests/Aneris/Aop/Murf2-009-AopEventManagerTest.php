<?php
namespace AnerisTest\EventManagerTest;

// Test Target Classes
use Aneris\Aop\EventManager;
use Aneris\Aop\EventListener;
use Aneris\Aop\Event;
use Aneris\Aop\EventInterface;
use Aneris\Container\Container;
use Aneris\Stdlib\Cache\CacheFactory;

$testresults = array();

class Foo1
{
    public function test1(EventInterface $e)
    {
        global $testresults;
        $testresults['test1'] = $e->getName();
        $testresults['test1_res'] = $e->getPreviousResult();
        $testresults['test1_event'] = $e;
        return 'test1';
    }
    public function test1_2(EventInterface $e)
    {
        global $testresults;
        $testresults['test1_2'] = $e->getName();
        $testresults['test1_2_event'] = $e;
    }
    public function test1_3(EventInterface $e)
    {
        global $testresults;
        $testresults['test1_3'] = $e->getName();
        $testresults['test1_3_args'] = $e->getArgs();
        $testresults['test1_3_event'] = $e;
    }
}
class Foo2
{
    public function test2(EventInterface $e)
    {
        global $testresults;
        $testresults['test2'] = $e->getName();
        $testresults['test2_res'] = $e->getPreviousResult();
        $testresults['test2_event'] = $e;
        return 'test2';
    }
    public function test2_2(EventInterface $e)
    {
        global $testresults;
        $testresults['test2_2'] = $e->getName();
        $testresults['test2_2_event'] = $e;
    }
    public function test2_3(EventInterface $e)
    {
        global $testresults;
        $testresults['test2_3'] = $e->getName();
        $testresults['test2_3_args'] = $e->getArgs();
        $testresults['test2_3_event'] = $e;
    }
}
class Foo3
{
    public function test3(EventInterface $e)
    {
        global $testresults;
        $testresults['test3'] = $e->getName();
        $testresults['test3_res'] = $e->getPreviousResult();
        $e->setBreak(true);
        return 'test3';
    }
}
class Foo1ex
{
    public function test1(EventInterface $e)
    {
        global $testresults;
        $testresults['test1ex'] = $e->getName();
        $testresults['test1ex_args'] = $e->getArgs();
        $testresults['test1ex_target'] = $e->getTarget();
        $testresults['test1ex_res'] = $e->getPreviousResult();
        return 'test1ex';
    }
}
class TestEvent extends Event
{}

class EventManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $testresults;
        $testresults = array();
    }

    public function testEvent()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $events->attach('Ev1',array($c1,'test1'));
        $c2 = new Foo2();
        $events->attach('Ev1',array($c2,'test2'));

        $events->notify('Ev1');

        $this->assertEquals('Ev1',$testresults['test1']);
        $this->assertEquals('Ev1',$testresults['test2']);
    }

    public function testEventBreak()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $events->attach('Ev1',array($c1,'test1'),20);
        $c3 = new Foo3();
        $events->attach('Ev1',array($c3,'test3'),10);
        $c2 = new Foo2();
        $events->attach('Ev1',array($c2,'test2'),5);

        $events->notify('Ev1');

        $this->assertEquals('Ev1',$testresults['test1']);
        $this->assertEquals('Ev1',$testresults['test3']);
        $this->assertEquals(false,isset($testresults['test2']));
    }

    public function testEventPriority()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $events->attach('Ev1',array($c1,'test1'),10);
        $c3 = new Foo3();
        $events->attach('Ev1',array($c3,'test3'),0);
        $c2 = new Foo2();
        $events->attach('Ev1',array($c2,'test2'),10);

        $events->notify('Ev1');

        $this->assertEquals('Ev1',$testresults['test1']);
        $this->assertEquals('Ev1',$testresults['test2']);
        $this->assertEquals('Ev1',$testresults['test3']);
    }

    public function testEventResult()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $events->attach('Ev1',array($c1,'test1'),10);
        $c2 = new Foo2();
        $events->attach('Ev1',array($c2,'test2'),10);
        $c3 = new Foo3();
        $events->attach('Ev1',array($c3,'test3'),0);

        $res = $events->notify('Ev1');

        $this->assertEquals(null,$testresults['test1_res']);
        $this->assertEquals('test1',$testresults['test2_res']);
        $this->assertEquals('test2',$testresults['test3_res']);
        $this->assertEquals('test3',$res);

    }

    public function testEventMultiple()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $events->attach('Ev1',array($c1,'test1'),10);
        $c2 = new Foo2();
        $events->attach('Ev1',array($c2,'test2'),10);
        $c3 = new Foo3();
        $events->attach('Ev1',array($c3,'test3'),0);

        $res = $events->notify('Ev1');

        $this->assertEquals(null,$testresults['test1_res']);
        $this->assertEquals('test1',$testresults['test2_res']);
        $this->assertEquals('test2',$testresults['test3_res']);
        $this->assertEquals('test3',$res);

        unset($testresults['test1_res']);
        unset($testresults['test2_res']);
        unset($testresults['test3_res']);

        $res = $events->notify('Ev1');

        $this->assertEquals(null,$testresults['test1_res']);
        $this->assertEquals('test1',$testresults['test2_res']);
        $this->assertEquals('test2',$testresults['test3_res']);
        $this->assertEquals('test3',$res);
    }

    public function testEventArgAndTarget()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1ex('textFoo1ex');
        $events->attach('Ev1',array($c1,'test1'),10);

        $res = $events->notify('Ev1',array('abc'),'TARGET','previous');

        $this->assertEquals('Ev1',$testresults['test1ex']);
        $this->assertEquals('previous',$testresults['test1ex_res']);
        $this->assertEquals(1,count($testresults['test1ex_args']));
        $this->assertEquals('abc',$testresults['test1ex_args'][0]);
        $this->assertEquals('TARGET',$testresults['test1ex_target']);
        $this->assertEquals('test1ex',$res);
    }

    public function testMultiEvent()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $c2 = new Foo2();

        $events->attach('ev1',array($c1,'test1'));
        $events->attach('ev1',array($c2,'test2'));

        $events->attach('ev2',array($c1,'test1_2'));
        $events->attach('ev2',array($c2,'test2_2'));

        $events->attach('ev3',array($c1,'test1_3'));
        $events->attach('ev3',array($c2,'test2_3'));

        $events->notify('ev1');
        $this->assertEquals('ev1',$testresults['test1']);
        $this->assertEquals('ev1',$testresults['test2']);
        $this->assertEquals(false,isset($testresults['test1_2']));
        $this->assertEquals(false,isset($testresults['test2_2']));

        $events->notify('ev2');

        $this->assertEquals('ev2',$testresults['test1_2']);
        $this->assertEquals('ev2',$testresults['test2_2']);

        $events->notify('ev3',array('arg1'=>1,'arg2'=>'str2'));

        $this->assertEquals('ev3',$testresults['test1_3']);
        $this->assertEquals('ev3',$testresults['test1_3']);
        $this->assertEquals(1,$testresults['test1_3_args']['arg1']);
        $this->assertEquals('str2',$testresults['test1_3_args']['arg2']);
        $this->assertEquals(1,$testresults['test2_3_args']['arg1']);
        $this->assertEquals('str2',$testresults['test2_3_args']['arg2']);
    }

    public function testEventEx()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1ex();
        $events->attach('ev1',array($c1,'test1'));
        $res = $events->notify('ev1',array('abc'),'TARGET','previous');

        $this->assertEquals('ev1',$testresults['test1ex']);
        $this->assertEquals('previous',$testresults['test1ex_res']);
        $this->assertEquals(1,count($testresults['test1ex_args']));
        $this->assertEquals('abc',$testresults['test1ex_args'][0]);
        $this->assertEquals('TARGET',$testresults['test1ex_target']);
        $this->assertEquals('test1ex',$res);
    }

    public function testAttachListener()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $events->attach('Ev1', new EventListener(array($c1,'test1')));
        $c2 = new Foo2();
        $events->attach('Ev1',new EventListener(array($c2,'test2')));

        $events->notify('Ev1');

        $this->assertEquals('Ev1',$testresults['test1']);
        $this->assertEquals('Ev1',$testresults['test2']);
    }

    public function testAttachListenerWithContainer()
    {
        global $testresults;

        $events = new EventManager();

        $events->attach('Ev1', new EventListener(null,'AnerisTest\EventManagerTest\Foo1','test1'));
        $events->attach('Ev1', new EventListener(null,'AnerisTest\EventManagerTest\Foo2','test2'));

        $events->setServiceLocator(new Container());
        $events->notify('Ev1');

        $this->assertEquals('Ev1',$testresults['test1']);
        $this->assertEquals('Ev1',$testresults['test2']);
    }

    public function testCachedAttachListenerWithContainer()
    {
        global $testresults;

        $events = new EventManager();

        $events->attach('Ev1', new EventListener(null,'AnerisTest\EventManagerTest\Foo1','test1'));
        $events->attach('Ev1', new EventListener(null,'AnerisTest\EventManagerTest\Foo2','test2'));

        CacheFactory::clearCache();
        $cache = CacheFactory::getInstance('/aop');
        $cache['eventmanager'] = $events;

        CacheFactory::$caches = array();
        $cache = CacheFactory::getInstance('/aop');
        $events = $cache['eventmanager'];
        $events->setServiceLocator(new Container());

        $events->notify('Ev1');
        $this->assertEquals('Ev1',$testresults['test1']);
        $this->assertEquals('Ev1',$testresults['test2']);
    }

    /**
     * @expectedException        Aneris\Aop\Exception\DomainException
     * @expectedExceptionMessage it need a service locator to instantiate callback if you want to set null to callback in the event "Ev1".
     */
    public function testAttachListenerWithoutContainer()
    {
        global $testresults;

        $events = new EventManager();

        $events->attach('Ev1', new EventListener(null,'AnerisTest\EventManagerTest\Foo1','test1'));
        $events->attach('Ev1', new EventListener(null,'AnerisTest\EventManagerTest\Foo2','test2'));

        $events->notify('Ev1');
    }

    /**
     * @expectedException        Aneris\Aop\Exception\InvalidArgumentException
     * @expectedExceptionMessage invalid callback in event "Ev1".
     */
    public function testInvalidCallback()
    {
        global $testresults;

        $events = new EventManager();

        $events->attach('Ev1', 'aaaa');
    }

    /**
     * @expectedException        Aneris\Aop\Exception\InvalidArgumentException
     * @expectedExceptionMessage invalid callback in event "Ev1".
     */
    public function testInvalidCallbackInListener()
    {
        global $testresults;

        $events = new EventManager();

        $events->attach('Ev1', new EventListener('aaaa'));

        $events->notify('Ev1');
    }

    public function testEventWithEvent()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $events->attach('Ev1',array($c1,'test1'));
        $c2 = new Foo2();
        $events->attach('Ev1',array($c2,'test2'));

        $event = new Event();
        $event->setName('Ev1');
        $events->notify($event);

        $this->assertEquals('Ev1',$testresults['test1']);
        $this->assertEquals('Ev1',$testresults['test2']);
    }

    /**
     * @expectedException        Aneris\Aop\Exception\InvalidArgumentException
     * @expectedExceptionMessage invalid event type.
     */
    public function testInvalidEventType()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $events->attach('Ev1',array($c1,'test1'));

        $events->notify(new \stdClass());
    }

    /**
     * @expectedException        Aneris\Aop\Exception\InvalidArgumentException
     * @expectedExceptionMessage invalid event name.
     */
    public function testInvalidEventName()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $events->attach('Ev1',array($c1,'test1'));

        $event = new Event();
        $event->setName(null);

        $events->notify($event);
    }

    /**
     * @expectedException        Aneris\Aop\Exception\DomainException
     * @expectedExceptionMessage invalid listener.: AnerisTest\EventManagerTest\Foo1::none
     */
    public function testInvalidListenerWithContainer()
    {
        global $testresults;

        $events = new EventManager();

        $events->attach('Ev1', new EventListener(null,'AnerisTest\EventManagerTest\Foo1','none'));

        $events->setServiceLocator(new Container());
        $events->notify('Ev1');
    }

    public function testArray()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $c2 = new Foo2();

        $events->attach('ev1',array($c1,'test1'));
        $events->attach('ev1',array($c2,'test2'));

        $events->attach('ev2',array($c1,'test1_2'));
        $events->attach('ev2',array($c2,'test2_2'));

        $events->attach('ev3',array($c1,'test1_3'));
        $events->attach('ev3',array($c2,'test2_3'));

        $events->notify(array('ev1','ev2','ev3'));

        $this->assertEquals('ev1',$testresults['test1']);
        $this->assertEquals('ev1',$testresults['test1_2']);
        $this->assertEquals('ev1',$testresults['test1_3']);
        $this->assertEquals('ev1',$testresults['test2']);
        $this->assertEquals('ev1',$testresults['test2_2']);
        $this->assertEquals('ev1',$testresults['test2_3']);
    }

    public function testArrayNameEvent()
    {
        global $testresults;

        $events = new EventManager();

        $c1 = new Foo1();
        $c2 = new Foo2();

        $events->attach('ev1',array($c1,'test1'));
        $events->attach('ev1',array($c2,'test2'));

        $events->attach('ev2',array($c1,'test1_2'));
        $events->attach('ev2',array($c2,'test2_2'));

        $events->attach('ev3',array($c1,'test1_3'));
        $events->attach('ev3',array($c2,'test2_3'));

        $event = new TestEvent();
        $event->setName(array('ev1','ev2','ev3'));
        $events->notify($event);

        $this->assertEquals($event,$testresults['test1_event']);
        $this->assertEquals($event,$testresults['test1_2_event']);
        $this->assertEquals($event,$testresults['test1_3_event']);
        $this->assertEquals($event,$testresults['test2_event']);
        $this->assertEquals($event,$testresults['test2_2_event']);
        $this->assertEquals($event,$testresults['test2_3_event']);
    }
}
