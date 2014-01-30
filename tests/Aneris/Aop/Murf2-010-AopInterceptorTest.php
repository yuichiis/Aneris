<?php
namespace AnerisTest\AopInterceptorTest;

use Aneris\Aop\Interceptor;
use Aneris\Aop\EventManager;
use Aneris\Aop\InterceptorBuilder;
use Aneris\Container\Container;

use Aneris\Container\Annotations\Proxy;

class BaseClass
{
    public $someVariable = 'someValue';

    public function doSomething($foo)
    {
        return 'someResult';
    }
}

class BaseClass2
{
    public $foo;

    public $someVariable = 'someValue';

    public function __construct(BaseClass $foo) {
        $this->foo = $foo;
    }
    public function getFoo()
    {
        return $this->foo;
    }
}

interface BaseClassInterface
{}
/**
* @Proxy('interface')
*/
class BaseClassWithIF implements BaseClassInterface
{
    public $someVariable = 'someValue';

    public function doSomething($foo)
    {
        return 'someResult';
    }
}

$BaseClass3Initialized = false;
class BaseClass3
{
    public $foo;

    public $someVariable = 'someValue';

    public function __construct(BaseClassInterface $foo) {
        global $BaseClass3Initialized;
        $BaseClass3Initialized = true;
        $this->foo = $foo;
    }
    public function getFoo()
    {
        return $this->foo;
    }
}

class AopInterceptorTest extends \PHPUnit_Framework_TestCase
{
    public function testExecutionInterfaceBased()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass';
        $component = $this->getMock('Aneris\Container\ComponentDefinition');
        $component->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($componentName));

        $definition = $this->getMock('Aneris\Container\Definition',null,array($componentName));

    	$instance = $this->getMock('AnerisTest\AopInterceptorTest\BaseClass');
    	$instance->expects($this->never())
    	        ->method('doSomething');

        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->once())
                ->method('instantiate')
                ->with( $this->equalTo($component))
                ->will( $this->returnValue($instance));

        $events = $this->getMock('Aneris\Aop\EventManager');
        $events->expects($this->atLeastOnce())
                ->method('notify')
                ->with($this->callback( function ($events) use ($componentName) {
                            foreach ($events as $event) {
                                $escape = str_replace('\\', '\\\\', $componentName);
                                if((preg_match('/(before|after|after\\-returning)\\:\\:execution\('.$escape.'\\:\\:doSomething\\(\\)\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\('.$escape.'\\:\\:\\*\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\(\\*\\:\\:doSomething\\(\\)\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\(\\*\\:\\:\\*\\)/', $event) )==false)
                                {
                                    return false;
                                }
                            }
                            return true;
                        }),
                	    $this->callback(function($args) {
                            if($args==array('arguments'=>array('foo')))
                                return true;
                            if($args==array('arguments'=>array('foo'),'returning'=>'someResult'))
                                return true;
                            return false;
                        }),
                	    $this->equalTo($instance),
                	    $this->anything());
        $events->expects($this->once())
                ->method('call')
                ->with($this->callback( function ($events) use ($componentName) {
                            foreach ($events as $event) {
                                $escape = str_replace('\\', '\\\\', $componentName);
                                if((preg_match('/around\\:\\:execution\('.$escape.'\\:\\:doSomething\\(\\)\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\('.$escape.'\\:\\:\\*\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\(\\*\\:\\:doSomething\\(\\)\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\(\\*\\:\\:\\*\\)/', $event) )==false)
                                {
                                    return false;
                                }
                            }
                            return true;
                        }),
                        $this->equalTo(null),
                        $this->equalTo($instance),
                        $this->callback(function($callback) use ($instance) {
                            list($object,$method) = $callback;
                            if($object!==$instance)
                                return false;
                            if($method!=='doSomething')
                                return false;
                            return true;
                        }),
                        $this->equalTo(array('foo')))
                ->will( $this->returnValue('someResult'));

        $interceptor = new Interceptor($container,$component,$events);

        $result = $interceptor->doSomething('foo');
        $this->assertEquals('someResult',$result);
    }

    public function testGet()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass';
        $component = $this->getMock('Aneris\Container\ComponentDefinition');
        $component->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($componentName));

        $definition = $this->getMock('Aneris\Container\Definition',null,array($componentName));

        $instance = $this->getMock('AnerisTest\AopInterceptorTest\BaseClass');

        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->once())
                ->method('instantiate')
                ->with( $this->equalTo($component))
                ->will( $this->returnValue($instance));

        $events = $this->getMock('Aneris\Aop\EventManager');
        $events->expects($this->atLeastOnce())
                ->method('notify')
                ->with($this->callback( function ($events) {
                            foreach ($events as $event) {
                                if(strpos($event, '::get(')===false)
                                    return false;
                            }
                            return true;
                        }),
                        $this->callback(function($args) {
                            if($args==array('name'=>'someVariable'))
                                return true;
                            if($args==array('name'=>'someVariable','value'=>'someValue'))
                                return true;
                            return false;
                        }),
                        $this->equalTo($instance),
                        $this->anything());

        $interceptor = new Interceptor($container,$component,$events);

        $result = $interceptor->someVariable;
        $this->assertEquals('someValue',$result);
    }

    public function testSet()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass';
        $component = $this->getMock('Aneris\Container\ComponentDefinition');
        $component->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($componentName));

        $definition = $this->getMock('Aneris\Container\Definition',null,array($componentName));

        $instance = $this->getMock('AnerisTest\AopInterceptorTest\BaseClass');

        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->once())
                ->method('instantiate')
                ->with( $this->equalTo($component))
                ->will( $this->returnValue($instance));

        $events = $this->getMock('Aneris\Aop\EventManager');
        $events->expects($this->atLeastOnce())
                ->method('notify')
                ->with($this->callback( function ($events) {
                            foreach ($events as $event) {
                                if(strpos($event, '::set(')===false)
                                    return false;
                            }
                            return true;
                        }),
                        $this->callback(function($args) {
                            if($args==array('name'=>'someVariable'))
                                return true;
                            if($args==array('name'=>'someVariable','value'=>'newValue'))
                                return true;
                            return false;
                        }),
                        $this->equalTo($instance),
                        $this->anything());

        $interceptor = new Interceptor($container,$component,$events);

        $this->assertEquals('someValue',$instance->someVariable);
        $interceptor->someVariable = 'newValue';
        $this->assertEquals('newValue',$instance->someVariable);
    }

    public function testIsset()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass';
        $component = $this->getMock('Aneris\Container\ComponentDefinition');
        $component->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($componentName));

        $definition = $this->getMock('Aneris\Container\Definition',null,array($componentName));

        $instance = $this->getMock('AnerisTest\AopInterceptorTest\BaseClass');

        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->once())
                ->method('instantiate')
                ->with( $this->equalTo($component))
                ->will( $this->returnValue($instance));

        $events = $this->getMock('Aneris\Aop\EventManager');

        $interceptor = new Interceptor($container,$component,$events);

        $res = isset($interceptor->someVariable);
        $this->assertTrue($res);
    }

    public function testUnset()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass';
        $component = $this->getMock('Aneris\Container\ComponentDefinition');
        $component->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($componentName));

        $definition = $this->getMock('Aneris\Container\Definition',null,array($componentName));

        $instance = $this->getMock('AnerisTest\AopInterceptorTest\BaseClass');

        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->once())
                ->method('instantiate')
                ->with( $this->equalTo($component))
                ->will( $this->returnValue($instance));

        $events = $this->getMock('Aneris\Aop\EventManager');

        $interceptor = new Interceptor($container,$component,$events);

        $this->assertTrue(isset($instance->someVariable));
        unset($interceptor->someVariable);
        $this->assertFalse(isset($instance->someVariable));
    }

    public function testExecutionInheritBased()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass';
        $component = $this->getMock('Aneris\Container\ComponentDefinition');
        $component->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($componentName));

        $definition = $this->getMock('Aneris\Container\Definition',null,array($componentName));

        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($componentName,'inheritance');
        //exit;
        include_once $builder->getInterceptorFileName($componentName,'inheritance');

        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->once())
                ->method('instantiate')
                ->with( $this->equalTo($component),
                        $this->equalTo(null),
                        $this->equalTo(null),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClassIHInterceptor')
                                return true;
                            return false;
                        })
                        );

        $events = $this->getMock('Aneris\Aop\EventManager');
        $events->expects($this->atLeastOnce())
                ->method('notify')
                ->with($this->callback( function ($events) use ($componentName) {
                            foreach ($events as $event) {
                                $escape = str_replace('\\', '\\\\', $componentName);
                                if((preg_match('/(before|after|after\\-returning)\\:\\:execution\('.$escape.'\\:\\:doSomething\\(\\)\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\('.$escape.'\\:\\:\\*\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\(\\*\\:\\:doSomething\\(\\)\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\(\\*\\:\\:\\*\\)/', $event) )==false)
                                {
                                    return false;
                                }
                            }
                            return true;
                        }),
                        $this->callback(function($args) {
                            if($args==array('arguments'=>array('foo')))
                                return true;
                            if($args==array('arguments'=>array('foo'),'returning'=>'someResult'))
                                return true;
                            return false;
                        }),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClassIHInterceptor')
                                return true;
                            return false;
                        }),
                        $this->anything());
        $events->expects($this->once())
                ->method('call')
                ->with($this->callback( function ($events) use ($componentName) {
                            foreach ($events as $event) {
                                $escape = str_replace('\\', '\\\\', $componentName);
                                if((preg_match('/around\\:\\:execution\('.$escape.'\\:\\:doSomething\\(\\)\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\('.$escape.'\\:\\:\\*\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\(\\*\\:\\:doSomething\\(\\)\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\(\\*\\:\\:\\*\\)/', $event) )==false)
                                {
                                    return false;
                                }
                            }
                            return true;
                        }),
                        $this->equalTo(null),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClassIHInterceptor')
                                return true;
                            return false;
                        }),
                        $this->callback(function($callback) {
                            list($instance,$method) = $callback;
                            if(get_class($instance)!='AnerisTest\AopInterceptorTest\BaseClassIHInterceptor')
                                return false;
                            if($method!=='__aop_method_doSomething')
                                return false;
                            return true;
                        }),
                        $this->equalTo(array('foo')))
                ->will( $this->returnValue('someResult'));

        $interceptorName = $builder->getInterceptorClassName($componentName,'inheritance');
        $interceptor = new $interceptorName($container,$component,$events);

        $result = $interceptor->doSomething('foo');
        $this->assertEquals('someResult',$result);
    }

    public function testExecutionInheritBasedWithConstructor()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass2';
        $component = $this->getMock('Aneris\Container\ComponentDefinition');
        $component->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($componentName));

        $definition = $this->getMock('Aneris\Container\Definition',null,array($componentName));

        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($componentName,'inheritance');
        //exit;
        include_once $builder->getInterceptorFileName($componentName,'inheritance');
        //echo $builder->getInterceptorDeclare($componentName);

        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->once())
                ->method('instantiate')
                ->with( $this->equalTo($component),
                        $this->equalTo(null),
                        $this->equalTo(null),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return true;
                            return false;
                        }),
                        $this->equalTo('__aop_construct')
                        );

        $events = $this->getMock('Aneris\Aop\EventManager');

        $events->expects($this->atLeastOnce())
                ->method('notify')
                ->with($this->callback( function ($events) use ($componentName) {
                            foreach ($events as $event) {
                                $escape = str_replace('\\', '\\\\', $componentName);
                                if((preg_match('/(before|after|after\\-returning)\\:\\:execution\('.$escape.'\\:\\:(getFoo)\\(\\)\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\('.$escape.'\\:\\:\\*\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\(\\*\\:\\:(getFoo)\\(\\)\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\(\\*\\:\\:\\*\\)/', $event) )==false)
                                {
                                    return false;
                                }
                            }
                            return true;
                        }),
                        $this->callback(function($args) {
                            if($args['arguments']!=array())
                                return false;
                            if(isset($args['returning']) && get_class($args['returning'])!='AnerisTest\AopInterceptorTest\BaseClass')
                                return false;
                            return true;
                        }),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return true;
                            return false;
                        }),
                        $this->anything());
        $events->expects($this->once())
                ->method('call')
                ->with($this->callback( function ($events) use ($componentName) {
                            foreach ($events as $event) {
                                $escape = str_replace('\\', '\\\\', $componentName);
                                if((preg_match('/around\\:\\:execution\('.$escape.'\\:\\:getFoo\\(\\)\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\('.$escape.'\\:\\:\\*\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\(\\*\\:\\:getFoo\\(\\)\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\(\\*\\:\\:\\*\\)/', $event) )==false)
                                {
                                    return false;
                                }
                            }
                            return true;
                        }),
                        $this->equalTo(null),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return true;
                            return false;
                        }),
                        $this->callback(function($callback) {
                            list($instance,$method) = $callback;
                            if(get_class($instance)!='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return false;
                            if($method!=='__aop_method_getFoo' && $method!=='__aop_method___construct')
                                return false;
                            return true;
                        }),
                        $this->callback(function($arguments) {
                            if($arguments==array())
                                return true;
                            if(get_class($arguments[0])=='AnerisTest\AopInterceptorTest\BaseClass')
                                return true;
                            return false;
                        }))
                ->will( $this->returnValue(new BaseClass()));

        $interceptorName = $builder->getInterceptorClassName($componentName,'inheritance');
        $interceptor = new $interceptorName($container,$component,$events);
        $interceptor->getFoo();
    }

    public function testCreateInheritBasedNonLazy()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass2';
        $component = $this->getMock('Aneris\Container\ComponentDefinition');
        $component->expects($this->once())
                ->method('getClassName')
                ->will($this->returnValue($componentName));

        $definition = $this->getMock('Aneris\Container\Definition',null,array($componentName));

        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($componentName,'inheritance');
        //exit;
        include_once $builder->getInterceptorFileName($componentName,'inheritance');
        //echo $builder->getInterceptorDeclare($componentName);

        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->once())
                ->method('instantiate')
                ->with( $this->equalTo($component),
                        $this->equalTo(null),
                        $this->equalTo(null),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return true;
                            return false;
                        }),
                        $this->equalTo('__aop_construct')
                        );

        $events = $this->getMock('Aneris\Aop\EventManager');

        $interceptorName = $builder->getInterceptorClassName($componentName,'inheritance');
        $interceptor = new $interceptorName($container,$component,$events);
    }

    public function testCreateInheritBasedLazy()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass2';
        $component = $this->getMock('Aneris\Container\ComponentDefinition');

        $definition = $this->getMock('Aneris\Container\Definition',null,array($componentName));

        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($componentName,'inheritance');
        //exit;
        include_once $builder->getInterceptorFileName($componentName,'inheritance');
        //echo $builder->getInterceptorDeclare($componentName);

        $container = $this->getMock('Aneris\Container\Container');
        $container->expects($this->never())
                ->method('instantiate');

        $events = $this->getMock('Aneris\Aop\EventManager');

        $interceptorName = $builder->getInterceptorClassName($componentName,'inheritance');
        $interceptor = new $interceptorName($container,$component,$events,true);
    }

    public function testInheritBasedInterceptorWithContainerInstantiate()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass2';
        $config = array (
            'auto_proxy' => 'component',
            'components' => array(
                $componentName => array(
                ),
            ),
        );
        $container = new Container($config);
        $aop = $this->getMock('Aneris\Aop\AopManager',null,array($container));
        $container->setProxyManager($aop);
        $component = $container->getComponentManager()->newComponent($componentName);

        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($componentName,'inheritance');
        include_once $builder->getInterceptorFileName($componentName,'inheritance');

        $events = $this->getMock('Aneris\Aop\EventManager');
        $events->expects($this->atLeastOnce())
                ->method('notify')
                ->with($this->callback( function ($events) use ($componentName) {
                            foreach ($events as $event) {
                                $escape = str_replace('\\', '\\\\', $componentName);
                                if((preg_match('/(before|after|after\\-returning)\\:\\:execution\('.$escape.'\\:\\:(__construct)\\(\\)\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\('.$escape.'\\:\\:\\*\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\(\\*\\:\\:(__construct)\\(\\)\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\(\\*\\:\\:\\*\\)/', $event) )==false)
                                {
                                    return false;
                                }
                            }
                            return true;
                        }),
                        $this->callback(function($args) {
                            if(count($args['arguments'])==1 && get_class($args['arguments'][0])=='AnerisTest\AopInterceptorTest\BaseClass')
                                return true;
                            return false;
                        }),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return true;
                            return false;
                        }),
                        $this->anything());
        $events->expects($this->once())
                ->method('call')
                ->with($this->callback( function ($events) use ($componentName) {
                            foreach ($events as $event) {
                                $escape = str_replace('\\', '\\\\', $componentName);
                                if((preg_match('/around\\:\\:execution\('.$escape.'\\:\\:(__construct)\\(\\)\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\('.$escape.'\\:\\:\\*\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\(\\*\\:\\:(__construct)\\(\\)\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\(\\*\\:\\:\\*\\)/', $event) )==false)
                                {
                                    return false;
                                }
                            }
                            return true;
                        }),
                        $this->equalTo(null),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return true;
                            return false;
                        }),
                        $this->callback(function($callback) {
                            list($instance,$method) = $callback;
                            if(get_class($instance)!='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return false;
                            if($method!=='__aop_method___construct')
                                return false;
                            $instance->foo = new BaseClass();
                            return true;
                        }),
                        $this->callback(function($arguments) {
                            if(get_class($arguments[0])!=='AnerisTest\AopInterceptorTest\BaseClass')
                                return false;
                            return true;
                        }));

        $interceptorName = $builder->getInterceptorClassName($componentName,'inheritance');
        $interceptor = new $interceptorName($container,$component,$events);
        $this->assertEquals('AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor',get_class($interceptor));
        $this->assertEquals('AnerisTest\AopInterceptorTest\BaseClass',get_class($interceptor->foo));
    }

    public function testLazyInheritBasedInterceptorWithContainerInstantiate()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass2';
        $config = array (
            'auto_proxy' => 'component',
            'components' => array(
                $componentName => array(
                ),
            ),
        );
        $container = new Container($config);
        $aop = $this->getMock('Aneris\Aop\AopManager',null,array($container));
        $container->setProxyManager($aop);
        $component = $container->getComponentManager()->newComponent($componentName);

        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($componentName,'inheritance');
        include_once $builder->getInterceptorFileName($componentName,'inheritance');

        $events = $this->getMock('Aneris\Aop\EventManager');
        $events->expects($this->atLeastOnce())
                ->method('notify')
                ->with($this->callback( function ($events) use ($componentName) {
                            foreach ($events as $event) {
                                $escape = str_replace('\\', '\\\\', $componentName);
                                if((preg_match('/(before|after|after\\-returning)\\:\\:execution\('.$escape.'\\:\\:(__construct|getFoo)\\(\\)\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\('.$escape.'\\:\\:\\*\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\(\\*\\:\\:(__construct|getFoo)\\(\\)\\)/', $event) ||
                                    preg_match('/(before|after|after\\-returning)\\:\\:execution\(\\*\\:\\:\\*\\)/', $event) )==false)
                                {
                                    return false;
                                }
                            }
                            return true;
                        }),
                        $this->callback(function($args) {
                            if(count($args['arguments'])==0)
                                return true;
                            if(count($args['arguments'])==1 && get_class($args['arguments'][0])=='AnerisTest\AopInterceptorTest\BaseClass')
                                return true;
                            return false;
                        }),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return true;
                            return false;
                        }),
                        $this->anything());
        $events->expects($this->atLeastOnce())
                ->method('call')
                ->with($this->callback( function ($events) use ($componentName) {
                            foreach ($events as $event) {
                                $escape = str_replace('\\', '\\\\', $componentName);
                                if((preg_match('/around\\:\\:execution\('.$escape.'\\:\\:(__construct|getFoo)\\(\\)\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\('.$escape.'\\:\\:\\*\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\(\\*\\:\\:(__construct|getFoo)\\(\\)\\)/', $event) ||
                                    preg_match('/around\\:\\:execution\(\\*\\:\\:\\*\\)/', $event) )==false)
                                {
                                    return false;
                                }
                            }
                            return true;
                        }),
                        $this->equalTo(null),
                        $this->callback(function($instance) {
                            if(get_class($instance)=='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return true;
                            return false;
                        }),
                        $this->callback(function($callback) {
                            list($instance,$method) = $callback;
                            if(get_class($instance)!='AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor')
                                return false;
                            if($method!=='__aop_method___construct' && $method!=='__aop_method_getFoo')
                                return false;
                            if($method=='__aop_method___construct')
                                $instance->foo = new BaseClass();
                            return true;
                        }),
                        $this->callback(function($arguments) {
                            if($arguments==array())
                                return true;
                            if(get_class($arguments[0])==='AnerisTest\AopInterceptorTest\BaseClass')
                                return true;
                            return false;
                        }));

        $interceptorName = $builder->getInterceptorClassName($componentName,'inheritance');
        $interceptor = new $interceptorName($container,$component,$events,true);
        $this->assertEquals('AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor',get_class($interceptor));
        $this->assertNull($interceptor->foo);
        $interceptor->getFoo();
        $this->assertEquals('AnerisTest\AopInterceptorTest\BaseClass',get_class($interceptor->foo));
    }

    public function testInheritBasedInterceptorWithAutoProxy()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass2';
        $config = array (
            'auto_proxy' => 'all',
            'components' => array(
                $componentName => array(
                ),
            ),
        );
        $container = new Container($config);
        $aop = $this->getMock('Aneris\Aop\AopManager',null,array($container));
        $container->setProxyManager($aop);
        $component = $container->getComponentManager()->newComponent($componentName);

        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($componentName,'inheritance');
        include_once $builder->getInterceptorFileName($componentName,'inheritance');

        $events = new EventManager();

        $interceptorName = $builder->getInterceptorClassName($componentName,'inheritance');
        $interceptor = new $interceptorName($container,$component,$events,true);
        $this->assertEquals('AnerisTest\AopInterceptorTest\BaseClass2IHInterceptor',get_class($interceptor));
        $this->assertNull($interceptor->foo);
        $interceptor->getFoo();
        $this->assertEquals('AnerisTest\AopInterceptorTest\BaseClassIHInterceptor',get_class($interceptor->foo));
    }

    public function testInterfaceBasedInterceptorWithAutoProxy()
    {
        $componentName = 'AnerisTest\AopInterceptorTest\BaseClass3';
        $config = array (
            'annotation_manager' => true,
            'auto_proxy' => 'all',
            'components' => array(
                $componentName => array(
                    'constructor_args' => array(
                        'foo' => array('ref'=>'AnerisTest\AopInterceptorTest\BaseClassWithIF'),
                    ),
                ),
            ),
        );
        $container = new Container($config);
        $aop = $this->getMock('Aneris\Aop\AopManager',null,array($container));
        $container->setProxyManager($aop);
        $component = $container->getComponentManager()->getComponent($componentName);

        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($componentName,'interface');
        include_once $builder->getInterceptorFileName($componentName,'interface');

        $events = new EventManager();

        $interceptorName = $builder->getInterceptorClassName($componentName,'interface');
        $interceptor = new $interceptorName($container,$component,$events,true);
        $this->assertEquals('AnerisTest\AopInterceptorTest\BaseClass3IFInterceptor',get_class($interceptor));
        global $BaseClass3Initialized;
        $this->assertFalse($BaseClass3Initialized);
        $this->assertEquals('AnerisTest\AopInterceptorTest\BaseClassWithIFIFInterceptor',get_class($interceptor->foo));
        $this->assertTrue($BaseClass3Initialized);
    }
}