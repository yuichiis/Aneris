<?php
namespace AnerisTest\PhpunitTest;

class SomeClass
{
    public function doSomething()
    {
        return 'doSomething';
    }
}

class SubClass extends SomeClass
{
}

class Subject
{
    protected $observers = array();
    protected $name;
    
    public function __construct($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
 
    public function attach(Observer $observer)
    {
        $this->observers[] = $observer;
    }
 
    public function doSomething()
    {
        // Do something.
        // ...
 
        // Notify observers that we did something.
        $this->notify('something');
    }
 
    public function doSomethingBad()
    {
        foreach ($this->observers as $observer) {
            $observer->reportError(42, 'Something bad happened', $this);
        }
    }
 
    protected function notify($argument)
    {
        foreach ($this->observers as $observer) {
            $observer->update($argument);
        }
    }
 
    // Other methods.
}
 
class Observer
{
    public function update($argument)
    {
        // Do something.
    }
 
    public function reportError($errorCode, $errorMessage, Subject $subject)
    {
        // Do something
    }
 
    // Other methods.
}

class PhpunitTest extends \PHPUnit_Framework_TestCase
{
    public function testAssertInstanceOf()
    {
        $this->assertInstanceOf('AnerisTest\PhpunitTest\SomeClass',new SubClass());
        $this->assertNotEquals('AnerisTest\PhpunitTest\SomeClass',get_class(new SubClass()));
    }
    public function testStub()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->getMock('AnerisTest\PhpunitTest\SomeClass');
 
        // Configure the stub.
        $stub->expects($this->once())
             ->method('doSomething')
             ->will($this->returnValue('foo'));
 
        // Calling $stub->doSomething() will now return
        // 'foo'.
        $this->assertEquals('foo', $stub->doSomething());
    }
    public function testStub2()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->getMock('AnerisTest\PhpunitTest\SomeClass');
 
        // Calling $stub->doSomething() will now return
        // 'foo'.
        $this->assertNull($stub->doSomething());
    }

    public function testObserversAreUpdated()
    {
        // Create a mock for the Observer class,
        // only mock the update() method.
        $observer = $this->getMock('AnerisTest\PhpunitTest\Observer', array('update'));
 
        // Set up the expectation for the update() method
        // to be called only once and with the string 'something'
        // as its parameter.
        $observer->expects($this->once())
                 ->method('update')
                 ->with($this->equalTo('something'));
 
        // Create a Subject object and attach the mocked
        // Observer object to it.
        $subject = new Subject('My subject');
        $subject->attach($observer);
 
        // Call the doSomething() method on the $subject object
        // which we expect to call the mocked Observer object's
        // update() method with the string 'something'.
        $subject->doSomething();
    }
    public function testErrorReported()
    {
        // Create a mock for the Observer class, mocking the
        // reportError() method
        $observer = $this->getMock('AnerisTest\PhpunitTest\Observer', array('reportError'));
 
        $observer->expects($this->once())
                 ->method('reportError')
                 ->with($this->greaterThan(0),
                        $this->stringContains('Something'),
                        $this->anything());
 
        $subject = new Subject('My subject');
        $subject->attach($observer);
 
        // The doSomethingBad() method should report an error to the observer
        // via the reportError() method
        $subject->doSomethingBad();
    }
    public function testErrorReported2()
    {
        // Create a mock for the Observer class, mocking the
        // reportError() method
        $observer = $this->getMock('AnerisTest\PhpunitTest\Observer', array('reportError'));
 
        $observer->expects($this->once())
                 ->method('reportError')
                 ->with($this->greaterThan(0),
                        $this->stringContains('Something'),
                        $this->callback(function($subject){
                          return is_callable(array($subject, 'getName')) &&
                                 $subject->getName() == 'My subject';
                        }));
 
        $subject = new Subject('My subject');
        $subject->attach($observer);
 
        // The doSomethingBad() method should report an error to the observer
        // via the reportError() method
        $subject->doSomethingBad();
    }
}