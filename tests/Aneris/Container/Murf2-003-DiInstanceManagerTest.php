<?php
namespace AnerisTest\InstanceManagerTest;

// Test Target Classes
use Aneris\Container\InstanceManager;
class CreateInstance0
{
}
class CreateInstance1
{
}

class ServiceManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testSetGetHas()
    {
        $im = new InstanceManager();
        $this->assertFalse($im->has('foo'));
        $this->assertFalse($im->get('foo'));
        $im->setInstance('foo',new CreateInstance0());
        $this->assertTrue($im->has('foo'));
        $this->assertEquals('AnerisTest\InstanceManagerTest\CreateInstance0',get_class($im->get('foo')));
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage Already registored:foo
     */
    public function testSetDuplicate()
    {
        $im = new InstanceManager();
        $im->setInstance('foo',new CreateInstance0());
        $im->setInstance('foo',new CreateInstance0());
    }
}
