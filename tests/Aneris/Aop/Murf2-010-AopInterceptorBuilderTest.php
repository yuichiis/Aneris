<?php
namespace AnerisTest\AopInterceptorBuilderTest;

use ReflectionClass;
use Aneris\Aop\InterceptorBuilder;
use Aneris\Stdlib\Cache\CacheFactory;
use ArrayObject;

use AcmeTest\Aop\TestArrayCallableInterface;
use AcmeTest\Aop\HaveArrayCallableClass;

interface TestInterface
{
    public function bar(TestInterface $value=null);
}

interface TestInterface2
{}

interface TestSubInterface extends TestInterface
{}

interface TestArrayInterface
{
    public function foo(array $array);
}

class DontHaveInterfeceClass
{
    function __construct($foo = null) {
        $this->foo = $foo;
    }
    public function none(TestInterface $value=null)
    {
        # code...
    }
}

class HaveInterfaceClass implements TestInterface
{
    function __construct($foo = null) {
        $this->foo = $foo;
    }
    public function bar(TestInterface $value=null)
    {
        # code...
    }
    public function none(TestInterface $value=null)
    {
        # code...
    }
}

class HaveInterfaceClass2 implements TestInterface,TestInterface2
{
    function __construct($foo = null) {
        $this->foo = $foo;
    }
    public function bar(TestInterface $value=null)
    {
        # code...
    }
}
class HaveSubInterfaceClass implements TestSubInterface
{
    function __construct($foo = null) {
        $this->foo = $foo;
    }
    public function bar(TestInterface $value=null)
    {
        # code...
    }
}
class HaveArrayClass implements TestArrayInterface
{
    public function foo(array $array)
    {

    }
}

class HaveInterfaceClass3 implements TestInterface2
{
    function __construct(TestInterface $foo = null) {
        $this->foo = $foo;
    }
    public function bar($value='',$value2='',$value3='')
    {
        # code...
    }
}

class NotHaveConstructor
{
    public function bar($value='')
    {
        # code...
    }
}

class HaveProtectedConstructor
{
    protected function __construct(TestInterface $foo = null)
    {
        $this->foo = $foo;
    }
}

interface HaveStaticFunctionInterface
{
    public static function staticFunction(array $foo);
    public function finalFunction(array $foo);
}
class HaveStaticFunction implements HaveStaticFunctionInterface
{
    public static function staticFunction(array $foo)
    {
        return $foo;
    }
    final public function finalFunction(array $foo)
    {
        return $foo;
    }
    protected function protectedFunction(array $foo)
    {
        return $foo;
    }
    private function privateFunction(array $foo)
    {
        return $foo;
    }
}

abstract class HaveAbstractFunction
{
    abstract public function abstractFunction(array $foo);
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
}
class SubInternal extends ArrayObject
{
    public function FunctionName($value='')
    {
        # code...
    }
}

class AopInterceptorBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClassDeclare()
    {
        $className = 'AnerisTest\AopInterceptorBuilderTest\DontHaveInterfeceClass';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class DontHaveInterfeceClassIFInterceptor extends Interceptor 
{

    public function none(\AnerisTest\AopInterceptorBuilderTest\TestInterface \$value=NULL)
    {
        return \$this->__call('none', array(\$value));
    }
}
EOD;
        $this->assertEquals($result,$builder->getInterfaceBasedInterceptorDeclare($className,'interface'));

        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveInterfaceClass';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class HaveInterfaceClassIFInterceptor extends Interceptor implements \AnerisTest\AopInterceptorBuilderTest\TestInterface
{

    public function bar(\AnerisTest\AopInterceptorBuilderTest\TestInterface \$value=NULL)
    {
        return \$this->__call('bar', array(\$value));
    }
    public function none(\AnerisTest\AopInterceptorBuilderTest\TestInterface \$value=NULL)
    {
        return \$this->__call('none', array(\$value));
    }
}
EOD;
        $this->assertEquals($result,$builder->getInterfaceBasedInterceptorDeclare($className,'interface'));

        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveInterfaceClass2';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class HaveInterfaceClass2IFInterceptor extends Interceptor implements \AnerisTest\AopInterceptorBuilderTest\TestInterface,\AnerisTest\AopInterceptorBuilderTest\TestInterface2
{

    public function bar(\AnerisTest\AopInterceptorBuilderTest\TestInterface \$value=NULL)
    {
        return \$this->__call('bar', array(\$value));
    }
}
EOD;
        $this->assertEquals($result,$builder->getInterfaceBasedInterceptorDeclare($className,'interface'));

        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveSubInterfaceClass';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class HaveSubInterfaceClassIFInterceptor extends Interceptor implements \AnerisTest\AopInterceptorBuilderTest\TestSubInterface
{

    public function bar(\AnerisTest\AopInterceptorBuilderTest\TestInterface \$value=NULL)
    {
        return \$this->__call('bar', array(\$value));
    }
}
EOD;
        $this->assertEquals($result,$builder->getInterfaceBasedInterceptorDeclare($className,'interface'));

        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveArrayClass';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class HaveArrayClassIFInterceptor extends Interceptor implements \AnerisTest\AopInterceptorBuilderTest\TestArrayInterface
{

    public function foo(array \$array)
    {
        return \$this->__call('foo', array(\$array));
    }
}
EOD;
        $this->assertEquals($result,$builder->getInterfaceBasedInterceptorDeclare($className,'interface'));

    }

    /**
     * @requires PHP 5.4.0
     */
    public function testGetCallableClassDeclare()
    {
        require_once ANERIS_TEST_RESOURCES.'/AcmeTest/Aop/class_with_callable.php';
        $className = 'AcmeTest\Aop\HaveArrayCallableClass';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AcmeTest\Aop;
use Aneris\Aop\Interceptor;
class HaveArrayCallableClassIFInterceptor extends Interceptor implements \AcmeTest\Aop\TestArrayCallableInterface
{

    public function foo(array \$array,callable \$callable)
    {
        return \$this->__call('foo', array(\$array,\$callable));
    }
}
EOD;
        $this->assertEquals($result,$builder->getInterfaceBasedInterceptorDeclare($className,'interface'));
    }

    public function testGetFileName()
    {
        $fileCachePath = CacheFactory::$fileCachePath;
        $className = 'AnerisTest\AopInterceptorBuilderTest\DontHaveInterfeceClass';
        $builder = new InterceptorBuilder();
        $this->assertEquals(
            $fileCachePath.
            '/Aneris/Aop/InterceptorBuilder/interceptors'.
            '/AnerisTest/AopInterceptorBuilderTest/DontHaveInterfeceClassIFInterceptor.php',
            $builder->getInterceptorFileName($className,'interface'));
        $this->assertEquals(
            $fileCachePath.
            '/Aneris/Aop/InterceptorBuilder/interceptors'.
            '/AnerisTest/AopInterceptorBuilderTest/DontHaveInterfeceClassIFInterceptor.php',
            $builder->getInterceptorFileName($className,'interface'));

    }

    public function testBuildAndInclude()
    {
        CacheFactory::clearCache();
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveInterfaceClass2';
        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($className,'interface');
        $filename = $builder->getInterceptorFileName($className,'interface');
        include $filename;
        $this->assertTrue(class_exists(
            'AnerisTest\AopInterceptorBuilderTest\HaveInterfaceClass2IFInterceptor'));
    }

    public function testInheritDeclareAndMultiParams()
    {
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveInterfaceClass3';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class HaveInterfaceClass3IHInterceptor extends \AnerisTest\AopInterceptorBuilderTest\HaveInterfaceClass3
{
    protected \$__aop_interceptor;
    public function __construct(\$container,\$component,\$events=null,\$lazy=null)
    {
        \$this->__aop_interceptor = new Interceptor(
            \$container,\$component,\$events,true,\$this,'__aop_construct');
        if(!\$lazy)
            \$this->__aop_interceptor->__aop_instantiate();
    }

    public function __aop_method___construct(\AnerisTest\AopInterceptorBuilderTest\TestInterface \$foo=NULL)
    {
        return parent::__construct(\$foo);
    }
    public function __aop_construct(\AnerisTest\AopInterceptorBuilderTest\TestInterface \$foo=NULL)
    {
        \$this->__aop_interceptor->__aop_before('__construct',array(\$foo));
        try {
            \$result = \$this->__aop_interceptor->__aop_around('__construct',array(\$foo),'__aop_method___construct');
        } catch(\\Exception \$e) {
            \$this->__aop_interceptor->__aop_afterThrowing('__construct',array(\$foo),\$e);
            throw \$e;
        }
        \$this->__aop_interceptor->__aop_afterReturning('__construct',array(\$foo),\$result);
        return \$result;
    }
    public function __aop_method_bar(\$value='',\$value2='',\$value3='')
    {
        return parent::bar(\$value,\$value2,\$value3);
    }
    public function bar(\$value='',\$value2='',\$value3='')
    {
        \$this->__aop_interceptor->__aop_before('bar',array(\$value,\$value2,\$value3));
        try {
            \$result = \$this->__aop_interceptor->__aop_around('bar',array(\$value,\$value2,\$value3),'__aop_method_bar');
        } catch(\\Exception \$e) {
            \$this->__aop_interceptor->__aop_afterThrowing('bar',array(\$value,\$value2,\$value3),\$e);
            throw \$e;
        }
        \$this->__aop_interceptor->__aop_afterReturning('bar',array(\$value,\$value2,\$value3),\$result);
        return \$result;
    }
}
EOD;
        //$result = str_replace(array("\r","\n"), array("",""), $result);
        $return = $builder->getInheritanceBasedInterceptorDeclare($className,null);
        //$return = str_replace(array("\r","\n"), array("",""), $return);
        $this->assertEquals($result,$return);
    }

    public function testInheritDeclareNotHaveConstructor()
    {
        $className = 'AnerisTest\AopInterceptorBuilderTest\NotHaveConstructor';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class NotHaveConstructorIHInterceptor extends \AnerisTest\AopInterceptorBuilderTest\NotHaveConstructor
{
    protected \$__aop_interceptor;
    public function __construct(\$container,\$component,\$events=null,\$lazy=null)
    {
        \$this->__aop_interceptor = new Interceptor(
            \$container,\$component,\$events,true,\$this,null);
        if(!\$lazy)
            \$this->__aop_interceptor->__aop_instantiate();
    }

    public function __aop_method_bar(\$value='')
    {
        return parent::bar(\$value);
    }
    public function bar(\$value='')
    {
        \$this->__aop_interceptor->__aop_before('bar',array(\$value));
        try {
            \$result = \$this->__aop_interceptor->__aop_around('bar',array(\$value),'__aop_method_bar');
        } catch(\\Exception \$e) {
            \$this->__aop_interceptor->__aop_afterThrowing('bar',array(\$value),\$e);
            throw \$e;
        }
        \$this->__aop_interceptor->__aop_afterReturning('bar',array(\$value),\$result);
        return \$result;
    }
}
EOD;
        //$result = str_replace(array("\r","\n"), array("",""), $result);
        $return = $builder->getInheritanceBasedInterceptorDeclare($className,'inheritance');
        //$return = str_replace(array("\r","\n"), array("",""), $return);
        $this->assertEquals($result,$return);
    }

    public function testInheritDeclareHaveProtectedConstructor()
    {
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveProtectedConstructor';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class HaveProtectedConstructorIHInterceptor extends \AnerisTest\AopInterceptorBuilderTest\HaveProtectedConstructor
{
    protected \$__aop_interceptor;
    public function __construct(\$container,\$component,\$events=null,\$lazy=null)
    {
        \$this->__aop_interceptor = new Interceptor(
            \$container,\$component,\$events,true,\$this,'__aop_construct');
        if(!\$lazy)
            \$this->__aop_interceptor->__aop_instantiate();
    }

    public function __aop_method___construct(\AnerisTest\AopInterceptorBuilderTest\TestInterface \$foo=NULL)
    {
        return parent::__construct(\$foo);
    }
    public function __aop_construct(\AnerisTest\AopInterceptorBuilderTest\TestInterface \$foo=NULL)
    {
        \$this->__aop_interceptor->__aop_before('__construct',array(\$foo));
        try {
            \$result = \$this->__aop_interceptor->__aop_around('__construct',array(\$foo),'__aop_method___construct');
        } catch(\\Exception \$e) {
            \$this->__aop_interceptor->__aop_afterThrowing('__construct',array(\$foo),\$e);
            throw \$e;
        }
        \$this->__aop_interceptor->__aop_afterReturning('__construct',array(\$foo),\$result);
        return \$result;
    }
}
EOD;
        //$result = str_replace(array("\r","\n"), array("",""), $result);
        $return = $builder->getInheritanceBasedInterceptorDeclare($className,'inheritance');
        //$return = str_replace(array("\r","\n"), array("",""), $return);
        $this->assertEquals($result,$return);
    }

    public function testBuildAndIncludeInheritDeclare()
    {
        CacheFactory::clearCache();
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveInterfaceClass3';
        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($className,'inheritance');
        $filename = $builder->getInterceptorFileName($className,'inheritance');
        include $filename;
        $this->assertTrue(class_exists(
            'AnerisTest\AopInterceptorBuilderTest\HaveInterfaceClass3IHInterceptor'));
    }

    public function testStaticAndFinalWithInterfaceDeclare()
    {
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveStaticFunction';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class HaveStaticFunctionIFInterceptor extends Interceptor implements \AnerisTest\AopInterceptorBuilderTest\HaveStaticFunctionInterface
{

    public static function staticFunction(array \$foo)
    {
        return parent::staticFunction(\$foo);
    }
    final public function finalFunction(array \$foo)
    {
        return \$this->__call('finalFunction', array(\$foo));
    }
}
EOD;
        //$result = str_replace(array("\r","\n"), array("",""), $result);
        $return = $builder->getInterfaceBasedInterceptorDeclare($className,'interface');
        //$return = str_replace(array("\r","\n"), array("",""), $return);
        $this->assertEquals($result,$return);        
    }

    public function testStaticAndFinalWithInterfaceInclude()
    {
        CacheFactory::clearCache();
        $a = new HaveStaticFunction();
        
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveStaticFunction';
        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($className,'interface');
        $filename = $builder->getInterceptorFileName($className,'interface');
        include $filename;
        $this->assertTrue(class_exists(
            'AnerisTest\AopInterceptorBuilderTest\HaveStaticFunctionIFInterceptor'));
        
    }

    public function testStaticAndFinalWithInheritDeclare()
    {
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveStaticFunction';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class HaveStaticFunctionIHInterceptor extends \AnerisTest\AopInterceptorBuilderTest\HaveStaticFunction
{
    protected \$__aop_interceptor;
    public function __construct(\$container,\$component,\$events=null,\$lazy=null)
    {
        \$this->__aop_interceptor = new Interceptor(
            \$container,\$component,\$events,true,\$this,null);
        if(!\$lazy)
            \$this->__aop_interceptor->__aop_instantiate();
    }

}
EOD;
        //$result = str_replace(array("\r","\n"), array("",""), $result);
        $return = $builder->getInheritanceBasedInterceptorDeclare($className,'inheritance');
        //$return = str_replace(array("\r","\n"), array("",""), $return);
        $this->assertEquals($result,$return);
    }
    public function testStaticAndFinalWithInheritInclude()
    {
        CacheFactory::clearCache();
        
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveStaticFunction';
        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($className,'inheritance');
        $filename = $builder->getInterceptorFileName($className,'inheritance');
        include $filename;
        $this->assertTrue(class_exists(
            'AnerisTest\AopInterceptorBuilderTest\HaveStaticFunctionIHInterceptor'));
        
    }

    public function testAbsractWithInheritDeclare()
    {
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveAbstractFunction';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class HaveAbstractFunctionIHInterceptor extends \AnerisTest\AopInterceptorBuilderTest\HaveAbstractFunction
{
    protected \$__aop_interceptor;
    public function __construct(\$container,\$component,\$events=null,\$lazy=null)
    {
        \$this->__aop_interceptor = new Interceptor(
            \$container,\$component,\$events,true,\$this,null);
        if(!\$lazy)
            \$this->__aop_interceptor->__aop_instantiate();
    }

}
EOD;
        //$result = str_replace(array("\r","\n"), array("",""), $result);
        $return = $builder->getInheritanceBasedInterceptorDeclare($className,'inheritance');
        //$return = str_replace(array("\r","\n"), array("",""), $return);
        $this->assertEquals($result,$return);
    }

    public function testHaveReferenceParamWithInterfaceDeclare()
    {
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveReferenceParam';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class HaveReferenceParamIFInterceptor extends Interceptor implements \AnerisTest\AopInterceptorBuilderTest\HaveReferenceParamInterface
{

    public function func(array &\$foo)
    {
        return \$this->__call('func', array(&\$foo));
    }
}
EOD;
        //$result = str_replace(array("\r","\n"), array("",""), $result);
        $return = $builder->getInterfaceBasedInterceptorDeclare($className,'interface');
        //$return = str_replace(array("\r","\n"), array("",""), $return);
        $this->assertEquals($result,$return);
    }

    public function testHaveReferenceParamWithInterfaceInclude()
    {
        CacheFactory::clearCache();
        
        $className = 'AnerisTest\AopInterceptorBuilderTest\HaveReferenceParam';
        $builder = new InterceptorBuilder();
        $builder->buildInterceptor($className,'interface');
        $filename = $builder->getInterceptorFileName($className,'interface');
        include $filename;
        $this->assertTrue(class_exists(
            'AnerisTest\AopInterceptorBuilderTest\HaveReferenceParamIFInterceptor'));
    }
/*
    public function testSubClassOfInternalWithInterfaceDeclare()
    {
        $className = 'AnerisTest\AopInterceptorBuilderTest\SubInternal';
        $builder = new InterceptorBuilder();
        $result = <<<EOD
<?php
namespace AnerisTest\AopInterceptorBuilderTest;
use Aneris\Aop\Interceptor;
class SubInternalIFInterceptor extends Interceptor implements \Countable,\Serializable,\ArrayAccess,\IteratorAggregate
{

    public function FunctionName(\$value='')
    {
        return \$this->__call('FunctionName', array(\$value));
    }
}
EOD;
        //$result = str_replace(array("\r","\n"), array("",""), $result);
        $return = $builder->getInterfaceBasedInterceptorDeclare($className,'interface');
        //$return = str_replace(array("\r","\n"), array("",""), $return);
        $this->assertEquals($result,$return);
    }
*/
}