<?php
namespace AnerisTest\DiContainerProxyTest;

use Aneris\Container\ProxyManagerInterface;
use Aneris\Container\ComponentDefinition;
use Aneris\Container\Container;

use Aneris\Container\Annotations\Proxy;

interface Param0Interface
{
}

class Param0 implements Param0Interface
{
}
class Param0Proxy extends Param0
{
}

/**
* @Proxy("interface")
*/
class Param0Ann implements Param0Interface
{
}

class TestProxyManager implements ProxyManagerInterface
{
    public function newProxy(Container $container,ComponentDefinition $component)
    {
        # code...
    }
}

class DiContainerProxyTest  extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {
        \Aneris\Stdlib\Cache\CacheFactory::clearCache();
    }

    public function testGetProxyMode()
    {
        $config = array(
            'annotation_manager' => true,
            'components' => array(
                'AnerisTest\DiContainerProxyTest\Param0' => array(
                    'proxy'=>'interface',
                ),
            ),
        );
        $di = new Container($config);
        $cm = $di->getComponentManager();
        $component = $cm->getComponent('AnerisTest\DiContainerProxyTest\Param0');
        $this->assertEquals('interface',$component->getProxyMode());

        $dm = $di->getDefinitionManager();
        $definition = $dm->getDefinition('AnerisTest\DiContainerProxyTest\Param0Ann');
        $this->assertEquals('interface',$definition->getProxyMode());
    }

    public function testNoAutoProxy()
    {
        $config = array(
        );
        $di = new Container($config);
        $proxyManager = $this->getMock('AnerisTest\DiContainerProxyTest\TestProxyManager');
        $proxyManager->expects($this->never())
                ->method('newProxy');
        $di->setProxyManager($proxyManager);
        $i = $di->get('AnerisTest\DiContainerProxyTest\Param0');
        $this->assertEquals('AnerisTest\DiContainerProxyTest\Param0',get_class($i));
    }

    public function testComponentAutoProxyDefault()
    {
        $config = array(
            'components' => array(
                'AnerisTest\DiContainerProxyTest\Param0' => array(
                ),
            ),
        );
        $di = new Container($config);
        $proxyManager = $this->getMock('AnerisTest\DiContainerProxyTest\TestProxyManager');
        $proxyManager->expects($this->once())
                ->method('newProxy')
                ->with($this->equalTo($di),
                    $this->callback(function($component) {
                        if($component->getName()=='AnerisTest\DiContainerProxyTest\Param0')
                            return true;
                        return false;
                    }))
                ->will($this->returnValue(new Param0Proxy()));
        $di->setProxyManager($proxyManager);
        $i = $di->get('AnerisTest\DiContainerProxyTest\Param0');
        $this->assertEquals('AnerisTest\DiContainerProxyTest\Param0Proxy',get_class($i));
    }

    public function testComponentAutoProxy()
    {
        $config = array(
            'auto_proxy' => 'component',
            'components' => array(
                'AnerisTest\DiContainerProxyTest\Param0' => array(
                ),
            ),
        );
        $di = new Container($config);
        $proxyManager = $this->getMock('AnerisTest\DiContainerProxyTest\TestProxyManager');
        $proxyManager->expects($this->once())
                ->method('newProxy')
                ->with($this->equalTo($di),
                    $this->callback(function($component) {
                        if($component->getName()=='AnerisTest\DiContainerProxyTest\Param0')
                            return true;
                        return false;
                    }))
                ->will($this->returnValue(new Param0Proxy()));
        $di->setProxyManager($proxyManager);
        $i = $di->get('AnerisTest\DiContainerProxyTest\Param0');
        $this->assertEquals('AnerisTest\DiContainerProxyTest\Param0Proxy',get_class($i));
    }

    public function testAllAutoProxy()
    {
        $config = array(
            'auto_proxy' => 'all',
        );
        $di = new Container($config);
        $proxyManager = $this->getMock('AnerisTest\DiContainerProxyTest\TestProxyManager');
        $proxyManager->expects($this->once())
                ->method('newProxy')
                ->with($this->equalTo($di),
                    $this->callback(function($component) {
                        if($component->getName()=='AnerisTest\DiContainerProxyTest\Param0')
                            return true;
                        return false;
                    }))
                ->will($this->returnValue(new Param0Proxy()));
        $di->setProxyManager($proxyManager);
        $i = $di->get('AnerisTest\DiContainerProxyTest\Param0');
        $this->assertEquals('AnerisTest\DiContainerProxyTest\Param0Proxy',get_class($i));
    }

    public function testExplicitAutoProxy()
    {
        $config = array(
            'auto_proxy' => 'explicit',
            'components' => array(
                'AnerisTest\DiContainerProxyTest\Param0' => array(
                    'proxy'=>'interface',
                ),
            ),
        );
        $di = new Container($config);
        $proxyManager = $this->getMock('AnerisTest\DiContainerProxyTest\TestProxyManager');
        $proxyManager->expects($this->once())
                ->method('newProxy')
                ->with($this->equalTo($di),
                    $this->callback(function($component) {
                        if($component->getName()=='AnerisTest\DiContainerProxyTest\Param0' &&
                            $component->getProxyMode()=='interface')
                            return true;
                        return false;
                    }),
                    $this->equalTo(array('mode'=>'interface')))
                ->will($this->returnValue(new Param0Proxy()));
        $di->setProxyManager($proxyManager);
        $i = $di->get('AnerisTest\DiContainerProxyTest\Param0');
        $this->assertEquals('AnerisTest\DiContainerProxyTest\Param0Proxy',get_class($i));
    }

    public function testExplicitAutoProxyNone()
    {
        $config = array(
            'auto_proxy' => 'explicit',
            'components' => array(
                'AnerisTest\DiContainerProxyTest\Param0' => array(
                ),
            ),
        );
        $di = new Container($config);
        $proxyManager = $this->getMock('AnerisTest\DiContainerProxyTest\TestProxyManager');
        $proxyManager->expects($this->never())
                ->method('newProxy');
        $di->setProxyManager($proxyManager);
        $i = $di->get('AnerisTest\DiContainerProxyTest\Param0');
        $this->assertEquals('AnerisTest\DiContainerProxyTest\Param0',get_class($i));
    }

    public function testExplicitAutoProxyAnnotation()
    {
        $config = array(
            'annotation_manager' => true,
            'auto_proxy' => 'explicit',
        );
        $di = new Container($config);
        $proxyManager = $this->getMock('AnerisTest\DiContainerProxyTest\TestProxyManager');
        $proxyManager->expects($this->once())
                ->method('newProxy')
                ->with($this->equalTo($di),
                    $this->callback(function($component) {
                        if($component->getName()=='AnerisTest\DiContainerProxyTest\Param0Ann' &&
                            $component->getProxyMode()==null)
                            return true;
                        return false;
                    }),
                    $this->equalTo(array('mode'=>'interface')))
                ->will($this->returnValue(new Param0Proxy()));
        $di->setProxyManager($proxyManager);
        $i = $di->get('AnerisTest\DiContainerProxyTest\Param0Ann');
        $this->assertEquals('AnerisTest\DiContainerProxyTest\Param0Proxy',get_class($i));
    }
}