<?php
namespace AnerisTest\EventCallTest;

use Aneris\Container\Container;
use Aneris\Aop\EventListener;
use Aneris\Aop\Event;

// Test Target Classes
use Aneris\Aop\EventManager;
use Aneris\Aop\EventProceeding;

class Logger
{
    protected $log;

    public function log($message)
    {
        $this->log[] = $message;
    }
    public function getLog()
    {
        return $this->log;
    }
    public function reset()
    {
        $this->log=null;
    }
}

class Ev1
{
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function invoke(EventProceeding $event,array $arguments)
    {
        if($event->getEvent()->getArg('arg1')!=='value1')
            throw new \Exception("Error");
        if($event->getEvent()->getTarget()->test!=='test')
            throw new \Exception("Error");
        $this->logger->log('ev1 front:'.$arguments[0]);
        $result = $event->proceed();
        $this->logger->log('ev1 back:'.$result);
        return $result;
    }
}

class Ev2
{
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function invoke(EventProceeding $event,array $arguments)
    {
        if($event->getEvent()->getArg('arg1')!=='value1')
            throw new \Exception("Error");
        if($event->getEvent()->getTarget()->test!=='test')
            throw new \Exception("Error");
        $this->logger->log('ev2 front:'.$arguments[0]);
        $result = $event->proceed();
        $this->logger->log('ev2 back:'.$result);
        return $result;
    }
}

class EventCallTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testEventProceeding()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();
        $events->attach('ev',function (EventProceeding $event,array $arguments) use ($test) {
            $test->log[] = 'ev1 front:'.$arguments[0];
            $result = $event->proceed();
            $test->log[] = 'ev1 back:'.$result;
            return $result;
        });
        $events->attach('ev',function (EventProceeding $event,array $arguments) use ($test) {
            $test->log[] = 'ev2 front:'.$arguments[0];
            $result = $event->proceed();
            $test->log[] = 'ev2 back:'.$result;
            return $result;
        });

        $event = new Event();
        $event->setName('ev');

        $terminator = function ($arg) use ($test) {
            $test->log[] = 'orig:'.$arg;
            return 'Out';
        };
        $arguments = array('In');
        $iterator = $events->fetch('ev')->getIterator();

        $proceed = new EventProceeding($event,$terminator,$arguments,$iterator);
        $this->assertEquals('Out',$proceed->proceed());
        $this->assertEquals('ev1 front:In',$test->log[0]);
        $this->assertEquals('ev2 front:In',$test->log[1]);
        $this->assertEquals('orig:In',$test->log[2]);
        $this->assertEquals('ev2 back:Out',$test->log[3]);
        $this->assertEquals('ev1 back:Out',$test->log[4]);
    }

    public function testEventManager()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();
        $events->attach('ev',function (EventProceeding $event,array $arguments) use ($test) {
            if($event->getEvent()->getArg('arg1')!=='value1')
                throw new \Exception("Error");
            if($event->getEvent()->getTarget()->test!=='test')
                throw new \Exception("Error");
            $test->log[] = 'ev1 front:'.$arguments[0];
            $result = $event->proceed();
            $test->log[] = 'ev1 back:'.$result;
            return $result;
        });
        $events->attach('ev',function (EventProceeding $event,array $arguments) use ($test) {
            if($event->getEvent()->getArg('arg1')!=='value1')
                throw new \Exception("Error");
            if($event->getEvent()->getTarget()->test!=='test')
                throw new \Exception("Error");
            $test->log[] = 'ev2 front:'.$arguments[0];
            $result = $event->proceed();
            $test->log[] = 'ev2 back:'.$result;
            return $result;
        });

        $terminator = function ($arg) use ($test) {
            $test->log[] = 'orig:'.$arg;
            return 'Out';
        };
        $arguments = array('In');

        $args = array('arg1'=>'value1');
        $target = new \stdClass();
        $target->test = 'test';

        $this->assertEquals('Out',$events->call('ev',$args,$target,$terminator,$arguments));
        $this->assertEquals('ev1 front:In',$test->log[0]);
        $this->assertEquals('ev2 front:In',$test->log[1]);
        $this->assertEquals('orig:In',$test->log[2]);
        $this->assertEquals('ev2 back:Out',$test->log[3]);
        $this->assertEquals('ev1 back:Out',$test->log[4]);
    }

    public function testEventManagerNonTerminator()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();
        $events->attach('ev',function (EventProceeding $event,array $arguments) use ($test) {
            if($event->getEvent()->getArg('arg1')!=='value1')
                throw new \Exception("Error");
            if($event->getEvent()->getTarget()->test!=='test')
                throw new \Exception("Error");
            $test->log[] = 'ev1 front:'.$arguments[0];
            $result = $event->proceed();
            $test->log[] = 'ev1 back:'.$result;
            return $result;
        });
        $events->attach('ev',function (EventProceeding $event,array $arguments) use ($test) {
            if($event->getEvent()->getArg('arg1')!=='value1')
                throw new \Exception("Error");
            if($event->getEvent()->getTarget()->test!=='test')
                throw new \Exception("Error");
            $test->log[] = 'ev2 front:'.$arguments[0];
            $result = $event->proceed();
            $test->log[] = 'ev2 back:'.$result;
            return $result;
        });

        $args = array('arg1'=>'value1');
        $target = new \stdClass();
        $target->test = 'test';
        $arguments = array('In');

        $this->assertEquals('In',$events->call('ev',$args,$target,null,$arguments));
        $result = array(
            'ev1 front:In',
            'ev2 front:In',
            'ev2 back:In',
            'ev1 back:In',
        );
        $this->assertEquals($result,$test->log);
    }

    /**
     * @expectedException        Aneris\Aop\Exception\DomainException
     * @expectedExceptionMessage invalid terminator callback on event "ev"
     */
    public function testEventManagerIllegalTerminator()
    {
        $events = new EventManager();
        $test = new \stdClass();
        $test->log = array();
        $events->attach('ev',function (EventProceeding $event,array $arguments) use ($test) {
            if($event->getEvent()->getArg('arg1')!=='value1')
                throw new \Exception("Error");
            if($event->getEvent()->getTarget()->test!=='test')
                throw new \Exception("Error");
            $test->log[] = 'ev1 front:'.$arguments[0];
            $result = $event->proceed();
            $test->log[] = 'ev1 back:'.$result[0];
            return $result;
        });
        $events->attach('ev',function (EventProceeding $event,array $arguments) use ($test) {
            if($event->getEvent()->getArg('arg1')!=='value1')
                throw new \Exception("Error");
            if($event->getEvent()->getTarget()->test!=='test')
                throw new \Exception("Error");
            $test->log[] = 'ev2 front:'.$arguments[0];
            $result = $event->proceed();
            $test->log[] = 'ev2 back:'.$result[0];
            return $result;
        });

        $args = array('arg1'=>'value1');
        $target = new \stdClass();
        $target->test = 'test';
        $terminator = 'IllegalTerminator';
        $arguments = array('In');

        $events->call('ev',$args,$target,$terminator,$arguments);
    }

    public function testEventManagerWithServiceLocator()
    {
        $container = new Container();
        $events = new EventManager();
        $events->setServiceLocator($container);
        $events->attach('ev',new EventListener(null,'AnerisTest\EventCallTest\Ev1','invoke'));
        $events->attach('ev',new EventListener(null,'AnerisTest\EventCallTest\Ev2','invoke'));
        $logger = $container->get('AnerisTest\EventCallTest\Logger');

        $terminator = function ($arg) use ($logger) {
            $logger->log('orig:'.$arg);
            return 'Out';
        };
        $arguments = array('In');

        $args = array('arg1'=>'value1');
        $target = new \stdClass();
        $target->test = 'test';

        $this->assertEquals('Out',$events->call('ev',$args,$target,$terminator,$arguments));
        $log = $logger->getLog();
        $this->assertEquals('ev1 front:In',$log[0]);
        $this->assertEquals('ev2 front:In',$log[1]);
        $this->assertEquals('orig:In',$log[2]);
        $this->assertEquals('ev2 back:Out',$log[3]);
        $this->assertEquals('ev1 back:Out',$log[4]);
    }
}
