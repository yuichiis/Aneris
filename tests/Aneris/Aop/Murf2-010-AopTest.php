<?php
namespace AnerisTest\AopTest;

use Aneris\Aop\AopEventsAwareInterface;
use Aneris\Aop\AspectInterface;
use Aneris\Aop\InterceptorEnableInterface;
use Aneris\Aop\EventManager;
use Aneris\Aop\EventManagerInterface;
use Aneris\Aop\AopManager;
use Aneris\Aop\EventInterface;
use Aneris\Aop\EventProceeding;

use Aneris\Container\Container;
use Aneris\Container\ModuleManager;

interface Param0Interface
{
}
interface Param1Interface
{
}

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

class Param0 implements Param0Interface
{
    public function getArg1($argVar)
    {
        $this->log('getArg1@Param0::'.$argVar);
    }
    public function log($message)
    {
        $this->logData[] = $message;
    }
    public function getThis()
    {
        return $this;
    }
}

class Param0InterceptorDummy
{
    function __construct(
            $container,
            $component,
            $eventManager,
            $lazy)
    {
    }
}

class Exception extends \Exception
{}

class Param1
{
    public function __construct(Param0Interface $arg1,Logger $logger)
    {
        $this->arg1 = $arg1;
        $this->logger = $logger;
    }

    public function getArg1($argVar)
    {
        $this->log('getArg1::'.$argVar);
        if($argVar=='throw')
            throw new Exception('hoge');
        return $this->arg1;
    }

    public function log($message)
    {
        $this->logger->log($message);
    }
}

class LabeledJoinPoint
{
    public function __construct(Param0Interface $arg1,Logger $logger)
    {
        $this->arg1 = $arg1;
        $this->logger = $logger;
    }

    public function setAopEvents(EventManagerInterface $aop)
    {
        $this->aop = $aop;
    }

    public function getArg1($argVar)
    {
        $this->aop->notify(
            'testpoint',
            array($argVar),
            $this,
            __CLASS__,
            __FUNCTION__);
        $this->log('getArg1::'.$argVar);
        return $this->arg1;
    }

    public function log($message)
    {
        $this->logger->log($message);
    }
}

class Param1Aspect implements AspectInterface
{
    const POINTCUT_GETARG1 = 'execution(*::getArg1())';
    protected $logger;

    public static function getAdvices()
    {
        return array(
            'before' => array(
                AspectInterface::ADVICE_BEFORE.'::'.self::POINTCUT_GETARG1),
            'afterReturning' => array(
                AspectInterface::ADVICE_AFTER_RETURNING.'::'.self::POINTCUT_GETARG1),
            'afterThrowing'  => array(
                 AspectInterface::ADVICE_AFTER_THROWING.'::execution(*::getArg1()::AnerisTest\AopTest\Exception)'),
            'after'  => array(
                AspectInterface::ADVICE_AFTER.'::'.self::POINTCUT_GETARG1),
            'around' => array(
                AspectInterface::ADVICE_AROUND.'::'.self::POINTCUT_GETARG1),
            'label' => array(
                AspectInterface::ADVICE_BEFORE.'::label(*::*::testpoint)'),
            'aroundLabel' => array(
                AspectInterface::ADVICE_AROUND.'::label(*::*::testpoint)'),
        );
    }

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    public function before(EventInterface $ev)
    {
        $args = $ev->getArgs();
        $message = 'Before call MESSAGE!::';
        if(isset($args['arguments'][0]))
            $message .= '(arg='.$args['arguments'][0].')';
        $this->logger->log($message);
    }

    public function around(EventProceeding $event,array $arguments)
    {
        $message = 'AROUND(frontend) call MESSAGE!::';
        if(isset($arguments[0]))
            $message .= '(arg='.$arguments[0].')';
        $this->logger->log($message);

        $returnValue = $event->proceed();

        $message = 'AROUND(backend) call MESSAGE!::';
        if(isset($arguments[0]))
            $message .= '(arg='.$arguments[0].')';
        if(isset($returnValue))
            $message .= '(ret='.get_class($returnValue).')';
        $this->logger->log($message);
        return $returnValue;
    }

    public function after(EventInterface $ev)
    {
        $args = $ev->getArgs();
        $message = 'After call MESSAGE!::';
        if(isset($args['arguments'][0]))
            $message .= '(arg='.$args['arguments'][0].')';
        if(isset($args['returning']))
            $message .= '(ret='.get_class($args['returning']).')';
        $this->logger->log($message);
    }

    public function afterReturning(EventInterface $ev)
    {
        $args = $ev->getArgs();
        $message = 'After-returning call MESSAGE!::';
        if(isset($args['arguments'][0]))
            $message .= '(arg='.$args['arguments'][0].')';
        if(isset($args['returning']))
            $message .= '(ret='.get_class($args['returning']).')';
        $this->logger->log($message);
    }

    public function afterThrowing(EventInterface $ev)
    {
        $args = $ev->getArgs();
        $message = 'After-throwing call MESSAGE!::';
        if(isset($args['arguments'][0]))
            $message .= '(arg='.$args['arguments'][0].')';
        if(isset($args['throwing']))
            $message .= '(throw='.get_class($args['throwing']).')';
        $this->logger->log($message);
    }

    public function label(EventInterface $ev)
    {
        $args = $ev->getArgs();
        $this->logger->log('Invoke call MESSAGE!::'.$args['arg1']);
    }
    public function aroundLabel(EventProceeding $event,array $arguments)
    {
        $message = 'AROUND(frontend) call MESSAGE!::';
        if(isset($arguments[0]))
            $message .= '(arg='.$arguments[0].')';
        $this->logger->log($message);

        $returnValue = $event->proceed();

        $message = 'AROUND(backend) call MESSAGE!::';
        if(isset($arguments[0]))
            $message .= '(arg='.$arguments[0].')';
        if(isset($returnValue))
            $message .= '(ret='.$returnValue.')';
        $this->logger->log($message);
        return $returnValue;
    }
}

class FooFactoryMode
{
    function __construct($logger) {
        $this->logger = $logger;
        $this->logger->log('Live:Foo');
    }
    public function getThis()
    {
        return $this;
    }
}

class FooFactoryModeFactory
{
    public static function factory($sm)
    {
        return new FooFactoryMode($sm->get('AnerisTest\AopTest\Logger'));
    }
}

class FooNeedConfig
{
    protected $config;
    function __construct(array $config) {
        $this->config = $config;
    }
    public function getConfig()
    {
        return $this->config;
    }
}

interface HaveStaticInterface
{
    public static function func($var);
}
class HaveStatic implements HaveStaticInterface
{
    public static function func($var)
    {
        return $var;
    }
}

interface HaveReferenceParamInterface
{
    public function func(array &$foo);
}
class HaveReferenceParam implements HaveReferenceParamInterface
{
    public function func(array &$foo)
    {
        $foo[] = 'foo';
    }

    public function funcOutOfInterface(array &$foo)
    {
        $foo[] = 'foo';
    }
}

class AopTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \Aneris\Stdlib\Cache\CacheFactory::clearCache();
    }

    public function testNewProxy()
    {
        $dummyFile = \Aneris\Stdlib\Cache\CacheFactory::$fileCachePath.'/dummy.php';
        $componentName = 'AnerisTest\AopTest\Param0';
        @unlink($dummyFile);
        $config = array();
        $component = $this->getMock('Aneris\Container\ComponentDefinition');
        $component->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($componentName));
        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->once())
                ->method('getAnnotationManager');
        $interceptorBuilder = $this->getMock('Aneris\Aop\InterceptorBuilder');
        $interceptorBuilder->expects($this->once())
                ->method('getInterceptorFileName')
                ->with($this->equalTo($componentName))
                ->will($this->returnValue($dummyFile));
        $interceptorBuilder->expects($this->once())
                ->method('buildInterceptor')
                ->with($this->equalTo($componentName),
                    $this->callback(function($mode) use ($dummyFile) {
                        if($mode!=null)
                            return false;
                        file_put_contents($dummyFile,"<?php\n");
                        return true;
                    }));
        $interceptorBuilder->expects($this->once())
                ->method('getInterceptorClassName')
                ->with($this->equalTo($componentName))
                ->will($this->returnValue('AnerisTest\AopTest\Param0InterceptorDummy'));
        $eventManager = $this->getMock('Aneris\Aop\EventManager');
        $aop = new AopManager($container,$eventManager,$interceptorBuilder);
        $aop->setConfig($config);
        $container->setProxyManager($aop);
        $interceptor = $aop->newProxy($container,$component);
        $this->assertEquals('AnerisTest\AopTest\Param0InterceptorDummy',get_class($interceptor));
    }

    public function testNewProxyAspect()
    {
        $componentName = 'AnerisTest\AopTest\Param1Aspect';
        $config = array (
            'auto_intercept' => 'all',
            'aspects' => array(
                $componentName => true,
            ),
        );
        $component = $this->getMock('Aneris\Container\ComponentDefinition');
        $component->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($componentName));
        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->once())
                ->method('getAnnotationManager');
        $container->expects($this->once())
                ->method('instantiate')
                ->with($this->equalTo($component),
                    $this->equalTo($componentName))
                ->will($this->returnValue(new $componentName(new Logger)));
        $interceptorBuilder = $this->getMock('Aneris\Aop\InterceptorBuilder');
        $interceptorBuilder->expects($this->never())
                ->method('getInterceptorFileName');
        $interceptorBuilder->expects($this->never())
                ->method('buildInterceptor');
        $interceptorBuilder->expects($this->never())
                ->method('getInterceptorClassName');
        $eventManager = $this->getMock('Aneris\Aop\EventManager');
        $aop = new AopManager($container,$eventManager,$interceptorBuilder);
        $aop->setConfig($config);
        $container->setProxyManager($aop);
        $interceptor = $aop->newProxy($container,$component);
        $this->assertEquals($componentName,get_class($interceptor));
    }

    public function testExecution()
    {
        $config = array (
            'aspects' => array(
                'AnerisTest\AopTest\Param1Aspect' => true,
            ),
            'components' => array(
                'AnerisTest\AopTest\Param1' => array(
                    'constructor_args' => array(
                        'arg1' => array('ref'=>'AnerisTest\AopTest\Param0'),
                    ),
                    //'proxy' => 'interface',
                ),
                'AnerisTest\AopTest\Logger' => array(
                    'proxy' => 'disable',
                ),
            ),
        );
        $container = new Container($config);
        $aop = new AopManager($container);
        $aop->setConfig($config);
        $container->setProxyManager($aop);
        $logger = $container->get('AnerisTest\AopTest\Logger');

        $result = array(
            'before::execution(*::getArg1())',
            'after-returning::execution(*::getArg1())',
            'after-throwing::execution(*::getArg1()::AnerisTest\AopTest\Exception)',
            'after::execution(*::getArg1())',
            'around::execution(*::getArg1())',
            'before::label(*::*::testpoint)',
            'around::label(*::*::testpoint)',
        );
        $this->assertEquals($result,$aop->getEventManager()->getEventNames());
        $i1 = $container->get('AnerisTest\AopTest\Param1');
        $this->assertNull($logger->getLog());

        $a = $i1->getArg1('A');
        $result = array(
            'Before call MESSAGE!::(arg=A)',
            'AROUND(frontend) call MESSAGE!::(arg=A)',
            'getArg1::A',
            'AROUND(backend) call MESSAGE!::(arg=A)(ret=AnerisTest\AopTest\Param0)',
            'After-returning call MESSAGE!::(arg=A)(ret=AnerisTest\AopTest\Param0)',
            'After call MESSAGE!::(arg=A)(ret=AnerisTest\AopTest\Param0)',
        );
        $this->assertEquals($result,$logger->getLog());
    }

    public function testAfterThrowing()
    {
        $config = array (
            'aspects' => array(
                'AnerisTest\AopTest\Param1Aspect' => true,
            ),
            'components' => array(
                'AnerisTest\AopTest\Param1' => array(
                    'constructor_args' => array(
                        'arg1' => array('ref'=>'AnerisTest\AopTest\Param0'),
                    ),
                    //'proxy' => 'interface',
                ),
                'AnerisTest\AopTest\Logger' => array(
                    'proxy' => 'disable',
                ),
            ),
        );
        $container = new Container($config);
        $aop = new AopManager($container);
        $aop->setConfig($config);
        $container->setProxyManager($aop);
        $logger = $container->get('AnerisTest\AopTest\Logger');

        $i1 = $container->get('AnerisTest\AopTest\Param1');
        $this->assertNull($logger->getLog());

        try {
            $a = $i1->getArg1('throw');
        } catch(Exception $e) {
            $this->assertEquals('AnerisTest\AopTest\Exception',get_class($e));
        }
        $result = array(
            'Before call MESSAGE!::(arg=throw)',
            'AROUND(frontend) call MESSAGE!::(arg=throw)',
            'getArg1::throw',
            'After-throwing call MESSAGE!::(arg=throw)(throw=AnerisTest\AopTest\Exception)',
            'After call MESSAGE!::(arg=throw)',
        );
        $this->assertEquals($result,$logger->getLog());
    }

    public function testNotifyLabel()
    {
        $config = array (
            'aspects' => array(
                'AnerisTest\AopTest\Param1Aspect' => true,
            ),
            'components' => array(
                'AnerisTest\AopTest\Logger' => array(
                    'proxy' => 'disable',
                ),
            ),
        );
        $container = new Container($config);
        $aop = new AopManager($container);
        $aop->setConfig($config);
        $container->setProxyManager($aop);
        $logger = $container->get('AnerisTest\AopTest\Logger');

        $args = array('arg1'=>'A');
        $target = new \stdClass();
        $aop->notify('testpoint',$args,$target,null,__CLASS__,__FUNCTION__);
        $result = array(
            'Invoke call MESSAGE!::A',
        );
        $this->assertEquals($result,$logger->getLog());
    }

    public function testCallLabel()
    {
        $config = array (
            'aspects' => array(
                'AnerisTest\AopTest\Param1Aspect' => true,
            ),
            'components' => array(
                'AnerisTest\AopTest\Logger' => array(
                    'proxy' => 'disable',
                ),
            ),
        );
        $container = new Container($config);
        $aop = new AopManager($container);
        $aop->setConfig($config);
        $container->setProxyManager($aop);
        $logger = $container->get('AnerisTest\AopTest\Logger');

        $args = array('arg1'=>'A');
        $target = new \stdClass();
        $arguments = array('test');
        $this->assertEquals('test',$aop->call('testpoint',$args,$target,null,$arguments,__CLASS__,__FUNCTION__));
        $result = array(
            'AROUND(frontend) call MESSAGE!::(arg=test)',
            'AROUND(backend) call MESSAGE!::(arg=test)(ret=test)',
        );
        $this->assertEquals($result,$logger->getLog());
    }

    public function testWithModuleManager()
    {
        $config = array (
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Aop\Module' => true,
                ),
            ),
            'aop' => array(
                'aspects' => array(
                    'AnerisTest\AopTest\Param1Aspect' => true,
                ),
            ),
            'container' => array(
                'components' => array(
                    'AnerisTest\AopTest\Logger' => array(
                        'proxy' => 'disable',
                    ),
                    'AnerisTest\AopTest\Param1' => array(
                        'constructor_args' => array(
                            'arg1' => array('ref'=>'AnerisTest\AopTest\Param0'),
                        ),
                        //'proxy' => 'interface',
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $container = $moduleManager->getServiceLocator();
        $logger = $container->get('AnerisTest\AopTest\Logger');

        $i1 = $container->get('AnerisTest\AopTest\Param1');
        $this->assertNull($logger->getLog());

        $a = $i1->getArg1('A');
        $result = array(
            'Before call MESSAGE!::(arg=A)',
            'AROUND(frontend) call MESSAGE!::(arg=A)',
            'getArg1::A',
            'AROUND(backend) call MESSAGE!::(arg=A)(ret=AnerisTest\AopTest\Param0)',
            'After-returning call MESSAGE!::(arg=A)(ret=AnerisTest\AopTest\Param0)',
            'After call MESSAGE!::(arg=A)(ret=AnerisTest\AopTest\Param0)',
        );
        $this->assertEquals($result,$logger->getLog());
    }

    public function testSwitchFactoryComponentMode()
    {
        $config = array (
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Aop\Module' => true,
                ),
            ),
            'container' => array(
                'components' => array(
                    'AnerisTest\AopTest\Param0' => array(
                    ),
                    'AnerisTest\AopTest\FooFactoryMode' => array(
                        'class' => 'AnerisTest\AopTest\FooFactoryMode',
                        'factory' => 'AnerisTest\AopTest\FooFactoryModeFactory::factory',
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $container = $moduleManager->getServiceLocator();

        $interceptor = $container->get('AnerisTest\AopTest\Param0');
        $instance = $interceptor->getThis();
        $this->assertEquals('AnerisTest\AopTest\Param0IHInterceptor',get_class($interceptor));
        $this->assertEquals('AnerisTest\AopTest\Param0IHInterceptor',get_class($instance));

        $interceptor = $container->get('AnerisTest\AopTest\FooFactoryMode');
        $instance = $interceptor->getThis();
        $this->assertEquals('AnerisTest\AopTest\FooFactoryModeIFInterceptor',get_class($interceptor));
        $this->assertEquals('AnerisTest\AopTest\FooFactoryMode',get_class($instance));
    }

    public function testSwitchComponentFactoryModeWithLazy()
    {
        $config = array (
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Aop\Module' => true,
                ),
            ),
            'container' => array(
                'components' => array(
                    'AnerisTest\AopTest\FooFactoryMode' => array(
                        'class' => 'AnerisTest\AopTest\FooFactoryMode',
                        'factory' => 'AnerisTest\AopTest\FooFactoryModeFactory::factory',
                        'lazy' => true,
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $container = $moduleManager->getServiceLocator();

        $logger = $container->get('AnerisTest\AopTest\Logger');
        $interceptor = $container->get('AnerisTest\AopTest\FooFactoryMode');
        $this->assertNull($logger->getLog());

        $instance = $interceptor->getThis();
        $this->assertEquals(array('Live:Foo'),$logger->getLog());
    }

    public function testDisableInterceptorEvent()
    {
        $config = array (
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Aop\Module' => true,
                ),
            ),
            'aop' => array(
                'disable_interceptor_event' => true,
                'aspects' => array(
                    'AnerisTest\AopTest\Param1Aspect' => true,
                ),
            ),
            'container' => array(
                'components' => array(
                    'AnerisTest\AopTest\Logger' => array(
                        'proxy' => 'disable',
                    ),
                    'AnerisTest\AopTest\Param1' => array(
                        'constructor_args' => array(
                            'arg1' => array('ref'=>'AnerisTest\AopTest\Param0'),
                        ),
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $container = $moduleManager->getServiceLocator();
        $logger = $container->get('AnerisTest\AopTest\Logger');

        $i1 = $container->get('AnerisTest\AopTest\Param1');
        $this->assertNull($logger->getLog());

        $a = $i1->getArg1('A');
        $result = array(
            'getArg1::A',
        );
        $this->assertEquals($result,$logger->getLog());
    }

    public function testDisableInterceptorEventWithLazy()
    {
        $config = array (
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Aop\Module' => true,
                ),
            ),
            'aop' => array(
                'disable_interceptor_event' => true,
            ),
            'container' => array(
                'components' => array(
                    'AnerisTest\AopTest\FooFactoryMode' => array(
                        'class' => 'AnerisTest\AopTest\FooFactoryMode',
                        'factory' => 'AnerisTest\AopTest\FooFactoryModeFactory::factory',
                        'lazy' => true,
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $container = $moduleManager->getServiceLocator();

        $logger = $container->get('AnerisTest\AopTest\Logger');
        $interceptor = $container->get('AnerisTest\AopTest\FooFactoryMode');
        $this->assertNull($logger->getLog());

        $instance = $interceptor->getThis();
        $this->assertEquals(array('Live:Foo'),$logger->getLog());
    }

    public function testInjectConfigByFactoryComponentMode()
    {
        $config = array (
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Aop\Module' => true,
                ),
            ),
            'container' => array(
                'components' => array(
                    'AnerisTest\AopTest\FooNeedConfig' => array(
                        'constructor_args' => array(
                            'config' => array('ref'=>'AnerisTest\AopTest\FooNeedConfig\Config'),
                        ),
                    ),
                    'AnerisTest\AopTest\FooNeedConfig\Config' => array(
                        'class' => 'array',
                        'factory' => 'Aneris\Container\ConfigurationFactory::factory',
                        'factory_args' => array('config'=>'something'),
                    ),
                ),
            ),
            'something' => array(
                'foo' => 'bar',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $container = $moduleManager->getServiceLocator();

        $foo = $container->get('AnerisTest\AopTest\FooNeedConfig');
        $this->assertEquals('AnerisTest\AopTest\FooNeedConfigIHInterceptor',get_class($foo));
        $this->assertEquals(array('foo'=>'bar'),$foo->getConfig());

        $fooConfig = $container->get('AnerisTest\AopTest\FooNeedConfig\Config');
        $this->assertEquals(array('foo'=>'bar'),$fooConfig);
    }

    /**
     * @expectedException        Aneris\Aop\Exception\DomainException
     * @expectedExceptionMessage class name is not specifed for interceptor in component "AnerisTest\AopTest\FooNeedConfig\Config".
     */
    public function testClassNameIsNotSpecifiedInjectConfigByFactoryComponentMode()
    {
        $config = array (
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Aop\Module' => true,
                ),
            ),
            'container' => array(
                'components' => array(
                    'AnerisTest\AopTest\FooNeedConfig' => array(
                        'constructor_args' => array(
                            'config' => array('ref'=>'AnerisTest\AopTest\FooNeedConfig\Config'),
                        ),
                    ),
                    'AnerisTest\AopTest\FooNeedConfig\Config' => array(
                        'factory' => 'Aneris\Container\ConfigurationFactory::factory',
                        'factory_args' => array('config'=>'something'),
                    ),
                ),
            ),
            'something' => array(
                'foo' => 'bar',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $container = $moduleManager->getServiceLocator();

        $foo = $container->get('AnerisTest\AopTest\FooNeedConfig');
    }

    /**
     * @expectedException        Aneris\Aop\Exception\DomainException
     * @expectedExceptionMessage static method is not supported to call a interceptor in "AnerisTest\AopTest\HaveStaticIFInterceptor".
     */
    public function testClassHaveStaticFunction()
    {
        $config = array (
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Aop\Module' => true,
                ),
            ),
            'container' => array(
                'components' => array(
                    'AnerisTest\AopTest\HaveStatic' => array(
                        'proxy' => 'interface',
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $container = $moduleManager->getServiceLocator();

        $i1 = $container->get('AnerisTest\AopTest\HaveStatic');
        $this->assertEquals('AnerisTest\AopTest\HaveStaticIFInterceptor',get_class($i1));
        $className = get_class($i1);
        $className::func('hoge');
    }

    public function testHaveReferenceParamWithInterfaceBasedInterceptor()
    {
        $config = array (
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Aop\Module' => true,
                ),
            ),
            'container' => array(
                'components' => array(
                    'AnerisTest\AopTest\HaveReferenceParam' => array(
                        'proxy' => 'interface',
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $container = $moduleManager->getServiceLocator();
        $i1 = $container->get('AnerisTest\AopTest\HaveReferenceParam');

        $array = array('A');
        $i1->func($array);
        $this->assertEquals(array('A','foo'),$array);
        $i1->funcOutOfInterface($array);
        $this->assertEquals(array('A','foo','foo'),$array);
    }
}
