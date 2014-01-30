<?php
namespace AnerisTest\DiDefinitionTest;

use Aneris\Annotation\AnnotationManager;
use Aneris\Stdlib\Entity\EntityTrait;
use Aneris\Stdlib\Entity\PropertyAccessPolicyInterface;

// Test Target Classes
use Aneris\Container\Definition;
use Aneris\Container\Annotations\Inject;
use Aneris\Container\Annotations\Named;
use Aneris\Container\Annotations\Scope;
use Aneris\Container\Annotations\PostConstruct;

class Param0
{
}
class Param1
{
    public function __construct(Param0 $arg1)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}
class Param2
{
    public function __construct($arg1)
    {
        $this->arg1 = $arg1;
    }
}

class Param3
{
    public function __construct(Param1 $arg1,Param2 $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}

class Param4
{
    public function __construct(Hogehoge $arg1)
    {
    }
}

class Param5
{
    public function __construct($arg1=null)
    {
        $this->arg1 = $arg1;
    }
}

class Param6
{
    const TESTCONST = 100;
    public function __construct($arg1=self::TESTCONST)
    {
        $this->arg1 = $arg1;
    }
}

class Param7
{
    public function __construct(Param0 $arg1=null)
    {
        $this->arg1 = $arg1;
    }
}

/**
* @Named("named0")
*/
class NamedInjection
{
    /**
    * @Inject({@Named(parameter="arg1",value="AnerisTest\DiDefinitionTest\Param0")})
    */
    public function __construct($arg1=null)
    {
        $this->arg1 = $arg1;
    }
    public function getArg1()
    {
        return $this->arg1;
    }
}
class SetterInjection
{
    /**
    * @Inject
    */
    public function setArg1(Param0 $arg1)
    {
        $this->arg1 = $arg1;
    }
    public function getArg1()
    {
        return $this->arg1;
    }
    // no setter
    public function setArg2(Param0 $arg2)
    {
        $this->arg2 = $arg2;
    }
}
class MultiArgInjection
{
    /**
    * @Inject
    */
    public function setArguments(Param0 $arg0,Param1 $arg1)
    {
        $this->arg0 = $arg0;
        $this->arg1 = $arg1;
    }
    public function getArg0()
    {
        return $this->arg0;
    }
    public function getArg1()
    {
        return $this->arg1;
    }
}

class SetterNamedInjection
{
    /**
    * @Inject({@Named(parameter="arg1",value="AnerisTest\DiDefinitionTest\Param0")})
    */
    public function setArg1($arg1)
    {
        $this->arg1 = $arg1;
    }
    public function getArg1()
    {
        return $this->arg1;
    }
}
class FieldAnnotationInjection
{
    /**
    * @Inject
    */
    protected $arg0;
    public function setArg0(Param0 $arg0)
    {
        $this->arg0 = $arg0;
    }
}
class FieldAnnotationNamedInjection
{
    /**
    * @Inject({@Named("AnerisTest\DiDefinitionTest\Param0")})
    */
    protected $arg0;
    public function setArg0($arg0)
    {
        $this->arg0 = $arg0;
    }
}
class FieldAnnotationNamedInjectionEntityTrait
{
    use EntityTrait;
    /**
    * @Inject({@Named("AnerisTest\DiDefinitionTest\Param0")})
    */
    protected $arg0;
}
class FieldAnnotationNamedInjectionPropertyAccess implements PropertyAccessPolicyInterface
{
    /**
    * @Inject({@Named("AnerisTest\DiDefinitionTest\Param0")})
    */
    public $arg0;
}
class FieldAnnotationAndSetterNamedInjection
{
    /**
    * @Inject
    */
    protected $arg0;
    /**
    * @Inject({@Named(parameter="arg0",value="AnerisTest\DiDefinitionTest\Param0")})
    */
    public function setArg0($arg0)
    {
        $this->arg0 = $arg0;
    }
}
class FieldAnnotationInjectionSetterNotfound
{
    /**
    * @Inject
    */
    protected $arg0;
    public function setArgOther($arg0)
    {
        $this->arg0 = $arg0;
    }
}
class ComplexInjection
{
    /**
    * @Inject({@Named(parameter="arg0",value="AnerisTest\DiDefinitionTest\Param0")})
    */
    public function __construct($arg0,Param1 $arg1)
    {
        $this->arg0 = $arg0;
        $this->arg1 = $arg1;
    }
    /**
    * @Inject({@Named(parameter="arg3",value="AnerisTest\DiDefinitionTest\Param3")})
    */
    public function setArg2(Param2 $arg2,$arg3)
    {
        $this->arg2 = $arg2;
        $this->arg3 = $arg3;
    }
    /**
    * @Inject({@Named(parameter="arg4",value="AnerisTest\DiDefinitionTest\Param4")})
    */
    public function setArg4($arg4)
    {
        $this->arg4 = $arg4;
    }
}
/**
* @Scope("prototype")
*/
class PrototypeScope
{
}

class PostConstructAnnotation
{
    protected $initialized;

    protected $arg0;

    /**
    * @Inject
    */
    public function setArg0($arg0=123)
    {
        $this->arg0 = $arg0;
        $this->initialized = null;
    }
    public function getArg0()
    {
        return $this->arg0;
    }

    /**
    * @PostConstruct
    */
    public function init()
    {
        $this->initialized = true;
    }
    public function is_initialized()
    {
        return $this->initialized;
    }
}

class DiDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testNoConstructor()
    {
        $def = new Definition('AnerisTest\DiDefinitionTest\Param0');

        $this->assertNull($def->getConstructor());

        $injects = $def->getInjects();

        $injects = array(
        );
        $this->assertEquals($injects,$def->getInjects());
    }

    public function testConstructorArg1()
    {
        $def = new Definition('AnerisTest\DiDefinitionTest\Param1');

        $this->assertEquals('__construct', $def->getConstructor());

        $injects = array(
            '__construct' => array(
                'arg1' => array('ref' => 'AnerisTest\DiDefinitionTest\Param0'),
            ),
        );
        $this->assertEquals($injects,$def->getInjects());
    }

    public function testConstructorArg1NonDef()
    {
        $def = new Definition('AnerisTest\DiDefinitionTest\Param2');

        $this->assertEquals('__construct', $def->getConstructor());

        $injects = array(
            '__construct' => array(
                'arg1' => array(),
            ),
        );
        $this->assertEquals($injects,$def->getInjects());
    }

    public function testConstructorArg2()
    {
        $def = new Definition('AnerisTest\DiDefinitionTest\Param3');

        $this->assertEquals('__construct', $def->getConstructor());

        $injects = array(
            '__construct' => array(
                'arg1' => array('ref' => 'AnerisTest\DiDefinitionTest\Param1'),
                'arg2' => array('ref' => 'AnerisTest\DiDefinitionTest\Param2'),
            ),
        );
        $this->assertEquals($injects,$def->getInjects());
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage AnerisTest\DiDefinitionTest\NotExist does not exist
     */
    public function testNotExist()
    {
        $def = new Definition('AnerisTest\DiDefinitionTest\NotExist');
        $a = $def->getConstructor();
    }

    public function testExport()
    {
        $def = new Definition('AnerisTest\DiDefinitionTest\Param1');
        $config = array(
            'class' => 'AnerisTest\DiDefinitionTest\Param1',
            'constructor' => '__construct',
            'injects' => array(
                '__construct' => array(
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionTest\Param0'),
                ),
            ),
        );
        $this->assertEquals($config,$def->export());

        //echo var_export($config);
    }

    public function testComplieAndLoad()
    {
        $def0 = new Definition('AnerisTest\DiDefinitionTest\Param1');
        $config0 = $def0->export();

        $def = new Definition($config0);
        $config = $def->export();
        $this->assertEquals($config0,$config);
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage invalid type of parameter "arg1". reason: Class AnerisTest\DiDefinitionTest\Hogehoge does not exist :
     */
    public function testNoArgumentType()
    {
        $def = new Definition('AnerisTest\DiDefinitionTest\Param4');

        $this->assertEquals('__construct', $def->getConstructor());

        $injects = $def->getInjects();
    }

    public function testDefaultValue()
    {
        $def = new Definition('AnerisTest\DiDefinitionTest\Param5');
        $injects = array(
            '__construct' => array(
                'arg1' => array('default' => null),
            ),
        );
        $this->assertEquals($injects,$def->getInjects());
    }

    public function testDefaultValueConstant()
    {
        $def = new Definition('AnerisTest\DiDefinitionTest\Param6');
        $injects = array(
            '__construct' => array(
                'arg1' => array('default' => 100),
            ),
        );
        $this->assertEquals($injects,$def->getInjects());
    }

    public function testTypeAndDefaultValue()
    {
        $def = new Definition('AnerisTest\DiDefinitionTest\Param7');
        $injects = array(
            '__construct' => array(
                'arg1' => array(
                    'ref' => 'AnerisTest\DiDefinitionTest\Param0',
                    'default' => null,
                ),
            ),
        );
        $this->assertEquals($injects,$def->getInjects());
    }

    public function testNamedInjection()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\NamedInjection',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\NamedInjection',
            'constructor' => '__construct',
            'injects' => array (
                '__construct' => array (
                    'arg1' => array(
                        'ref'=>'AnerisTest\DiDefinitionTest\\Param0',
                        'default' => null,
                    ),
                ),
            ),
            'name' => 'named0',
        );
        $this->assertEquals($exp,$def->export());
    }
    public function testSetterInjection()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\SetterInjection',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\SetterInjection',
            'constructor' => null,
            'injects' => array (
                'setArg1' => array (
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }
    public function testMultiArgInjection()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\MultiArgInjection',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\MultiArgInjection',
            'constructor' => null,
            'injects' => array (
                'setArguments' => array (
                    'arg0' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param1'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }

    public function testSetterNamedInjection()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\SetterNamedInjection',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\SetterNamedInjection',
            'constructor' => null,
            'injects' => array (
                'setArg1' => array (
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }
    public function testFieldAnnotationInjection()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\FieldAnnotationInjection',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\FieldAnnotationInjection',
            'constructor' => null,
            'injects' => array (
                'setArg0' => array (
                    'arg0' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }
    public function testFieldAnnotationNamedInjection()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\FieldAnnotationNamedInjection',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\FieldAnnotationNamedInjection',
            'constructor' => null,
            'injects' => array (
                'setArg0' => array (
                    'arg0' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }
    public function testFieldAnnotationNamedInjectionEntityTrait()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\FieldAnnotationNamedInjectionEntityTrait',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\FieldAnnotationNamedInjectionEntityTrait',
            'constructor' => null,
            'injects' => array (
                'setArg0' => array (
                    'arg0' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }
    public function testFieldAnnotationNamedInjectionPropertyAccess()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\FieldAnnotationNamedInjectionPropertyAccess',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\FieldAnnotationNamedInjectionPropertyAccess',
            'constructor' => null,
            'injects' => array (
                'setArg0' => array (
                    'arg0' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }
    public function testFieldAnnotationAndSetterNamedInjection()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\FieldAnnotationAndSetterNamedInjection',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\FieldAnnotationAndSetterNamedInjection',
            'constructor' => null,
            'injects' => array (
                'setArg0' => array (
                    'arg0' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }
    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage setter is not found to inject for "arg0":
     */
    public function testFieldAnnotationInjectionSetterNotFound()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\FieldAnnotationInjectionSetterNotFound',$annotationManager);
    }
    public function testComplexInjection()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\ComplexInjection',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\ComplexInjection',
            'constructor' => '__construct',
            'injects' => array (
                '__construct' => array (
                    'arg0' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param1'),
                ),
                'setArg2' => array (
                    'arg2' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param2'),
                    'arg3' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param3'),
                ),
                'setArg4' => array (
                    'arg4' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param4'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }

    public function testScope()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\PrototypeScope',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\PrototypeScope',
            'constructor' => null,
            'injects' => array (
            ),
            'scope' => 'prototype',
        );
        $this->assertEquals($exp,$def->export());
        $this->assertEquals('prototype',$def->getScope());
    }

    public function testPostConstruct()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\PostConstructAnnotation',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\PostConstructAnnotation',
            'constructor' => null,
            'injects' => array (
                'setArg0' => array(
                    'arg0' => array('default'=>123),
                )
            ),
            'init_method' => 'init',
        );
        $this->assertEquals($exp,$def->export());
        $this->assertEquals('init',$def->getinitMethod());
    }

    public function testAddMethod()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\SetterInjection',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\SetterInjection',
            'constructor' => null,
            'injects' => array (
                'setArg1' => array (
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());

        $this->assertTrue($def->addMethod('setArg2'));
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\SetterInjection',
            'constructor' => null,
            'injects' => array (
                'setArg1' => array (
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
                'setArg2' => array (
                    'arg2' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());

        $this->assertFalse($def->addMethod('none'));
        $this->assertEquals($exp,$def->export());
    }

    public function testAddMethodForce()
    {
        $annotationManager = AnnotationManager::factory();
        $def = new Definition('AnerisTest\DiDefinitionTest\SetterInjection',$annotationManager);
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\SetterInjection',
            'constructor' => null,
            'injects' => array (
                'setArg1' => array (
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());

        $def->addMethodForce('none1','argx');
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\SetterInjection',
            'constructor' => null,
            'injects' => array (
                'setArg1' => array (
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
                'none1' => array (
                    'argx' => array(),
                ),
            ),
        );

        $def->addMethodForce('none2','argy','hoge');
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionTest\\SetterInjection',
            'constructor' => null,
            'injects' => array (
                'setArg1' => array (
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionTest\\Param0'),
                ),
                'none1' => array (
                    'argx' => array(),
                ),
                'none2' => array (
                    'argy' => array('ref'=>'hoge'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }
}
