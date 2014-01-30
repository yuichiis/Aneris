<?php
namespace AnerisTest\MvcDispatcherTest;

use Aneris\Mvc\ViewManagerInterface;
use Aneris\Mvc\Context;

// Test Target Classes
use Aneris\Mvc\Dispatcher;

class FooController
{
    public function indexAction()
    {
        return __METHOD__;
    }

    public function barAction()
    {
        return __METHOD__;
    }

    public function test2()
    {
        return __METHOD__;
    }
}

class Foo2Controller
{
    public function barAction()
    {
        return __METHOD__;
    }
}

class Foo3
{
    public function barAction()
    {
        return __METHOD__;
    }

    public function boo()
    {
        return __METHOD__;
    }
}

class ViewManager implements ViewManagerInterface
{
    public function render($response,$template,$templatePaths,$context)
    {
        return;
    }
}

class MvcDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testNormalNoInvokables()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $param['namespace'] = 'AnerisTest\MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $context = new Context(null,null,$param);

        $dispatcher = new Dispatcher();

        $response = $dispatcher->dispatch($context);
        $this->assertEquals('AnerisTest\MvcDispatcherTest\FooController::barAction', $response);
    }

    public function testNormalInvokables()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $context = new Context(null,null,$param);

        $config = array(
                    'invokables' => array(
                        'foo' => 'AnerisTest\MvcDispatcherTest\Foo3',
                    ),
        );

        $dispatcher = new Dispatcher();
        $dispatcher->setConfig($config);

        $response = $dispatcher->dispatch($context);
        $this->assertEquals('AnerisTest\MvcDispatcherTest\Foo3::barAction', $response);
    }

    public function testNormalInvokablesWithNamespace()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $param['namespace'] = 'BooBoo\BarBar';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $context = new Context(null,null,$param);

        $config['invokables'] = array(
            'BooBoo\BarBar\foo' => 'AnerisTest\MvcDispatcherTest\Foo3',
        );

        $dispatcher = new Dispatcher();
        $dispatcher->setConfig($config);
        $response = $dispatcher->dispatch($context);
        $this->assertEquals('AnerisTest\MvcDispatcherTest\Foo3::barAction', $response);
    }

/*
    public function testNotInheritAbstractController()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $request = new Aneris\Http\Request();
        $param['namespace'] = 'AnerisTest\MvcDispatcherTest';
        $param['controller'] = 'foo2';
        $param['action'] = 'bar';

        $dispatcher = new Dispatcher();
        $response = $dispatcher->dispatch($param,$request);
        $this->assertEquals('AnerisTest\MvcDispatcherTest\Foo2Controller::barAction', $response);
    }
*/
    
    /**
     * @expectedException        Aneris\Mvc\Exception\PageNotFoundException
     * @expectedExceptionMessage A controller is not found:AnerisTest\MvcDispatcherTest\BazController
     */
    public function testNoControllerNoInvokables()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $param['namespace'] = 'AnerisTest\MvcDispatcherTest';
        $param['controller'] = 'baz';
        $param['action'] = 'bar';
        $context = new Context(null,null,$param);

        $dispatcher = new Dispatcher();
        $response = $dispatcher->dispatch($context);
        $this->assertEquals('AnerisTest\MvcDispatcherTest\FooController::barAction', $response);
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\PageNotFoundException
     * @expectedExceptionMessage A controller is not found:AnerisTest\MvcDispatcherTest\BarBarController
     */
    public function testNoControllerInvokablesWithNamespace()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $param['namespace'] = 'BooBoo\BarBar';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $context = new Context(null,null,$param);

        $config['invokables'] = array(
            'BooBoo\BarBar\foo' => 'AnerisTest\MvcDispatcherTest\BarBarController',
        );

        $dispatcher = new Dispatcher();
        $dispatcher->setConfig($config);
        $response = $dispatcher->dispatch($context);
        $this->assertEquals('AnerisTest\MvcDispatcherTest\Foo3::barAction', $response);
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\PageNotFoundException
     * @expectedExceptionMessage A controller is not found in the invokables configuration:foo
     */
    public function testNormalInvokablesNotfound()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        //$param['namespace'] = 'AnerisTest\MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $context = new Context(null,null,$param);

        $config['invokables'] = array(
            'fooboo' => 'AnerisTest\MvcDispatcherTest\Foo3',
        );

        $dispatcher = new Dispatcher();
        $dispatcher->setConfig($config);
        $response = $dispatcher->dispatch($context);
        $this->assertEquals('AnerisTest\MvcDispatcherTest\Foo3::barAction', $response);
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\PageNotFoundException
     * @expectedExceptionMessage A controller is not found in the invokables configuration:foo
     */
    public function testNormalInvokablesNotfoundWithNamespace()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        //$param['namespace'] = 'AnerisTest\MvcDispatcherTest';
        $param['namespace'] = 'fooName\Space';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $context = new Context(null,null,$param);

        $config['invokables'] = array(
            'booName\Space\fooboo' => 'AnerisTest\MvcDispatcherTest\Foo3',
        );

        $dispatcher = new Dispatcher();
        $dispatcher->setConfig($config);
        $response = $dispatcher->dispatch($context);
        $this->assertEquals('AnerisTest\MvcDispatcherTest\Foo3::barAction', $response);
    }

    public function testClassMethod()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        //$param['namespace'] = 'AnerisTest\MvcDispatcherTest';
        $param['namespace'] = 'fooName\Space';
        $param['class']  = 'AnerisTest\MvcDispatcherTest\Foo3';
        $param['method'] = 'boo';
        $context = new Context(null,null,$param);

        $config['invokables'] = array(
            'booName\Space\fooboo' => 'AnerisTest\MvcDispatcherTest\Foo3',
        );

        $dispatcher = new Dispatcher();
        $dispatcher->setConfig($config);
        $response = $dispatcher->dispatch($context);
        $this->assertEquals('AnerisTest\MvcDispatcherTest\Foo3::boo', $response);
    }
}
