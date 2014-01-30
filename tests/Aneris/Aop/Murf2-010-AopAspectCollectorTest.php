<?php
namespace AnerisTest\AopAspectCollectorTest;

use Aneris\Container\Container;
use Aneris\Stdlib\Cache\CacheFactory;

use Aneris\Aop\AopManager;
use Aneris\Aop\Annotations\Before;
use Aneris\Aop\Annotations\AfterReturning;
use Aneris\Aop\Annotations\AfterThrowing;
use Aneris\Aop\Annotations\After;
use Aneris\Aop\Annotations\Around;

class TestPlainOldPhpObjectAspect
{
	public function foo1($event)
	{
		return __METHOD__;
	}
	public function foo2($event)
	{
		return __METHOD__;
	}
}

class TestAdviceFuncAspect
{
	public static function getAdvices()
	{
    	return	array(
   			'foo1' => 'before::label(*::*::pointcut1)',
   			'foo2' => array(
   				'before::label(*::*::pointcut2)',
   				'before::label(*::*::pointcut3)',
   			),
   		);
	}

	public function foo1($event)
	{
		return __METHOD__;
	}
	public function foo2($event)
	{
		return __METHOD__;
	}
}

class TestAnnotationAspect
{
	/**
	* @Before("execution(*::Log1())")
	*/
	public function foo1($event)
	{
		return __METHOD__;
	}
	/**
	* @Before("execution(*::Log2())")
	*/
	public function foo2($event)
	{
		return __METHOD__;
	}
}
class TestEtcAnnotationAspect
{
    /**
    * @Before("execution(*::Log1())")
    * @AfterReturning("execution(*::Log1())")
    * @AfterThrowing("execution(*::Log1()::Exception)")
    * @After("execution(*::Log1())")
    * @Around("execution(*::Log1())")
    */
    public function foo1($event)
    {
        return __METHOD__;
    }
}

class TestAnnotationSyntaxErrorAspect
{
    /**
    * @Before("brabrabra")
    */
    public function foo1($event)
    {
        return __METHOD__;
    }
}

class AopAspectCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testPointcutFormat()
    {
        $container = new Container();
        $aop = new AopManager($container);
        $this->assertNull($aop->checkPointcutSyntax('before::execution(Foo\Bar::boo())'));
        $this->assertNull($aop->checkPointcutSyntax('before::execution(Fo-o_Bar1::b_o-o1())'));
        $this->assertNull($aop->checkPointcutSyntax('before::execution(*::boo())'));
        $this->assertNull($aop->checkPointcutSyntax('before::execution(Foo\Bar::*)'));
        $this->assertNull($aop->checkPointcutSyntax('before::execution(*::*)'));
        $this->assertEquals('invalid pointcut format.: "execution(*::*)"',$aop->checkPointcutSyntax('execution(*::*)'));
        $this->assertEquals('unknown advice type.: "foo".',$aop->checkPointcutSyntax('foo::execution(*::*)'));
        $this->assertEquals('unknown join point type.: "bar".',$aop->checkPointcutSyntax('before::bar(*::*)'));
        $this->assertEquals('invalid path format.: "path"',$aop->checkPointcutSyntax('before::execution(path)'));
        $this->assertEquals('invalid class name format.: "$"',$aop->checkPointcutSyntax('before::execution($::*)'));
        $this->assertEquals('invalid method name format.: "$"',$aop->checkPointcutSyntax('before::execution(*::$)'));

        $this->assertNull($aop->checkPointcutSyntax('after::execution(*::*)'));
        $this->assertNull($aop->checkPointcutSyntax('after-throwing::execution(*::*::*)'));
        $this->assertNull($aop->checkPointcutSyntax('after-throwing::execution(*::*::Exception)'));
        $this->assertNull($aop->checkPointcutSyntax('after-returning::execution(*::*)'));
        $this->assertNull($aop->checkPointcutSyntax('after::execution(*::*)'));
        $this->assertNull($aop->checkPointcutSyntax('around::execution(*::*)'));
        $this->assertNull($aop->checkPointcutSyntax('before::get(*::*)'));
        $this->assertNull($aop->checkPointcutSyntax('before::set(*::*)'));
        $this->assertNull($aop->checkPointcutSyntax('before::label(*::*::abc)'));

        $this->assertEquals('exception type or label name is not specified.: "*::*"',$aop->checkPointcutSyntax('after-throwing::execution(*::*)'));
        $this->assertEquals('exception type or label name is not specified.: "*::*"',$aop->checkPointcutSyntax('before::label(*::*)'));
        $this->assertEquals('invalid path format.: "*::*::*"',$aop->checkPointcutSyntax('before::execution(*::*::*)'));
        $this->assertEquals('invalid path format.: "*"',$aop->checkPointcutSyntax('before::execution(*)'));
        $this->assertEquals('invalid path format.: "*::*::*::*"',$aop->checkPointcutSyntax('after-throwing::execution(*::*::*::*)'));
    }

    public function testAddPlainPhpAspect()
    {
    	$container = new Container();
    	$aop = new AopManager($container);
    	$aop->addAspect(
    		'AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect',
    		array(
    			'foo1' => 'before::label(*::*::pointcut1)',
    			'foo2' => array(
    				'before::label(*::*::pointcut2)',
    				'before::label(*::*::pointcut3)',
    			),
    		)
    	);
    	$events = $aop->getEventManager();
    	$res = $events->notify('before::label(*::*::pointcut1)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect::foo1',$res);

    	$res = $events->notify('before::label(*::*::pointcut2)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect::foo2',$res);

    	$res = $events->notify('before::label(*::*::pointcut3)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect::foo2',$res);
    }

    /**
     * @expectedException        Aneris\Aop\Exception\DomainException
     * @expectedExceptionMessage invalid pointcut format.: "brabrabra": in a aspect advice definition of AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect::foo1
     */
    public function testAddAspectSyntaxError()
    {
        $container = new Container();
        $aop = new AopManager($container);
        $aop->addAspect(
            'AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect',
            array(
                'foo1' => 'brabrabra',
            )
        );
    }

    public function testAddPlainPhpAspectByConfig()
    {
    	$config = array(
    		'aspects' => array(
        		'AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect' => array(
        			'foo1' => 'before::label(*::*::pointcut1)',
        			'foo2' => array(
        				'before::label(*::*::pointcut2)',
        				'before::label(*::*::pointcut3)',
        			),
        		),
    		),
    	);
    	$container = new Container($config);
    	$aop = new AopManager($container);
    	$aop->setConfig($config);
    	$events = $aop->getEventManager();
    	$res = $events->notify('before::label(*::*::pointcut1)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect::foo1',$res);

    	$res = $events->notify('before::label(*::*::pointcut2)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect::foo2',$res);

    	$res = $events->notify('before::label(*::*::pointcut3)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect::foo2',$res);
    }

    public function testAddAdviceFuncAspect()
    {
    	$container = new Container();
    	$aop = new AopManager($container);
    	$aop->addAspect('AnerisTest\AopAspectCollectorTest\TestAdviceFuncAspect');
    	$events = $aop->getEventManager();
    	$res = $events->notify('before::label(*::*::pointcut1)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestAdviceFuncAspect::foo1',$res);

    	$res = $events->notify('before::label(*::*::pointcut2)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestAdviceFuncAspect::foo2',$res);

    	$res = $events->notify('before::label(*::*::pointcut3)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestAdviceFuncAspect::foo2',$res);
    }

    public function testAddAdviceFuncAspectByConfig()
    {
    	$config = array(
    		'aspects' => array(
        		'AnerisTest\AopAspectCollectorTest\TestAdviceFuncAspect' => true,
        	),
    	);
    	$container = new Container($config);
    	$aop = new AopManager($container);
    	$aop->setConfig($config);
    	$events = $aop->getEventManager();
    	$res = $events->notify('before::label(*::*::pointcut1)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestAdviceFuncAspect::foo1',$res);

    	$res = $events->notify('before::label(*::*::pointcut2)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestAdviceFuncAspect::foo2',$res);

    	$res = $events->notify('before::label(*::*::pointcut3)');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestAdviceFuncAspect::foo2',$res);
    }

    public function testAddAnnotationAspect()
    {
    	$container = new Container();
    	$container->setAnnotationManagerName(true);
    	$aop = new AopManager($container);
    	$aop->addAspect('AnerisTest\AopAspectCollectorTest\TestAnnotationAspect');
    	$events = $aop->getEventManager();
    	$res = $events->notify('before::execution(*::Log1())');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestAnnotationAspect::foo1',$res);

    	$res = $events->notify('before::execution(*::Log2())');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestAnnotationAspect::foo2',$res);
    }

    /**
     * @expectedException        Aneris\Aop\Exception\DomainException
     * @expectedExceptionMessage invalid pointcut format.: "before::brabrabra":
     */
    public function testAddAnnotationAspectSyntaxError()
    {
        $container = new Container();
        $container->setAnnotationManagerName(true);
        $aop = new AopManager($container);
        $aop->addAspect('AnerisTest\AopAspectCollectorTest\TestAnnotationSyntaxErrorAspect');
    }

    public function testAddAnnotationAspectByConfig()
    {
    	$config = array(
    		'annotation_manager' => true,
    		'aspects' => array(
        		'AnerisTest\AopAspectCollectorTest\TestAnnotationAspect' => true,
        	),
    	);
    	$container = new Container($config);
    	$aop = new AopManager($container);
    	$aop->setConfig($config);
    	$events = $aop->getEventManager();
    	$res = $events->notify('before::execution(*::Log1())');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestAnnotationAspect::foo1',$res);

    	$res = $events->notify('before::execution(*::Log2())');
    	$this->assertEquals('AnerisTest\AopAspectCollectorTest\TestAnnotationAspect::foo2',$res);
    }

    public function testAddEtcAnnotationAspectByConfig()
    {
        $config = array(
            'annotation_manager' => true,
            'aspects' => array(
                'AnerisTest\AopAspectCollectorTest\TestEtcAnnotationAspect' => true,
            ),
        );
        $container = new Container($config);
        $aop = new AopManager($container);
        $aop->setConfig($config);
        $result = array(
            'before::execution(*::Log1())',
            'after-returning::execution(*::Log1())',
            'after-throwing::execution(*::Log1()::Exception)',
            'around::execution(*::Log1())',
        );
        $events = $aop->getEventManager();
        $this->assertEquals($result,$events->getEventNames());
    }

    /**
     * @expectedException        Aneris\Aop\Exception\DomainException
     * @expectedExceptionMessage advice is not found in a class.: AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect
     */
    public function testAspectNotfound()
    {
    	$container = new Container();
    	$container->setAnnotationManagerName(true);
    	$aop = new AopManager($container);
    	$aop->addAspect('AnerisTest\AopAspectCollectorTest\TestPlainOldPhpObjectAspect');
    }

    public function testScanAspect()
    {
    	$config = array(
    		'annotation_manager' => true,
    		'component_paths' => array(
        		ANERIS_TEST_RESOURCES.'/AcmeTest/Aop/Aspect' => true,
        	),
    	);
    	$container = new Container($config);
    	$aop = new AopManager($container);
    	$aop->setConfig($config);
    	$container->setProxyManager($aop);
    	$container->scanComponents();
    	$events = $aop->getEventManager();
        $result = array(
            'before::execution(*::Log1())',
            'before::execution(*::Log2())',
        );
        $this->assertEquals($result,$events->getEventNames());
        $res = $events->notify('before::execution(*::Log1())');
        $this->assertEquals('AcmeTest\Aop\Aspect\TestAnnotationAspect::foo1',$res);

        $res = $events->notify('before::execution(*::Log2())');
        $this->assertEquals('AcmeTest\Aop\Aspect\TestAnnotationAspect::foo2',$res);
    }

    public function testScanAspectWithCache()
    {
        CacheFactory::clearCache();
    	$config = array(
    		'annotation_manager' => true,
    		'component_paths' => array(
        		ANERIS_TEST_RESOURCES.'/AcmeTest/Aop/Aspect' => true,
        	),
    	);
    	$container = new Container($config);
    	$aop = new AopManager($container);
    	$aop->setConfig($config);
    	$container->setProxyManager($aop);
    	$container->scanComponents();

        CacheFactory::$caches = array();

    	$container = new Container($config);
    	$aop = new AopManager($container);
    	$aop->setConfig($config);
    	$container->setProxyManager($aop);

    	$events = $aop->getEventManager();
        $result = array(
            'before::execution(*::Log1())',
            'before::execution(*::Log2())',
        );
        $this->assertEquals($result,$events->getEventNames());
    	$res = $events->notify('before::execution(*::Log1())');
    	$this->assertEquals('AcmeTest\Aop\Aspect\TestAnnotationAspect::foo1',$res);

    	$res = $events->notify('before::execution(*::Log2())');
    	$this->assertEquals('AcmeTest\Aop\Aspect\TestAnnotationAspect::foo2',$res);
    }
}