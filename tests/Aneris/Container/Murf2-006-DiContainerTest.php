<?php
namespace AnerisTest\DiContainerTest;

use Aneris\Stdlib\Entity\PropertyAccessPolicyInterface;
use Aneris\Stdlib\Entity\EntityAbstract;

use Aneris\Container\ServiceLocatorInterface;

// Test Target Classes
use Aneris\Container\Container;
use Aneris\Container\Annotations\Inject;
use Aneris\Container\Annotations\Named;
use Aneris\Container\Annotations\Scope;
use Aneris\Container\Annotations\PostConstruct;

interface Param0Interface
{
}
interface Param1Interface
{
}

class Param0 implements Param0Interface
{
}

class Param1
{
    public function __construct(Param0 $arg1)
    {
        $this->arg1 = $arg1;
    }

    public function getArg1()
    {
        return $this->arg1;
    }
}

class Param1Dash implements Param1Interface
{
    public function __construct(Param0Interface $arg1)
    {
        $this->arg1 = $arg1;
    }

    public function getArg1()
    {
        return $this->arg1;
    }
}

class Param2
{
    public function __construct($arg1)
    {
        $this->arg1 = $arg1;
    }

    public function getArg1()
    {
        return $this->arg1;
    }
}

class Param3
{
    public function __construct(Param1 $arg1,Param2 $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }

    public function getArg1()
    {
        return $this->arg1;
    }

    public function getArg2()
    {
        return $this->arg2;
    }
}

class Param3Dash
{
    public function __construct(Param1Interface $arg1,Param2 $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }

    public function getArg1()
    {
        return $this->arg1;
    }

    public function getArg2()
    {
        return $this->arg2;
    }
}

interface CombiServiceInterface {}
class CombiService implements CombiServiceInterface
{
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setArgs($args)
    {
        $this->args = $args;
    }

    public function getArgs()
    {
        return $this->args;
    }
}
class CombiServiceFactory
{
    public static function factory(ServiceLocatorInterface $sm,$componentName=null,$args=null)
    {
        $instance = new CombiService();
        $instance->setData('Created from Factory');
        $instance->setName($componentName);
        $instance->setArgs($args);
        return $instance;
    }
}

class CombiDiClass1
{
    protected $service;

    public function __construct(CombiServiceInterface $service)
    {
        $this->service = $service;
    }

    public function getService()
    {
        return $this->service;
    }
}

class CombiDiClass2
{
    protected $service0;
    protected $service1;

    public function __construct(CombiServiceInterface $service0, CombiDiClass1 $service1)
    {
        $this->service0 = $service0;
        $this->service1 = $service1;
    }

    public function getService0()
    {
        return $this->service0;
    }

    public function getService1()
    {
        return $this->service1;
    }
}

class SetterInjectionClass
{
    public function setArg0(Param0Interface $arg0)
    {
        $this->arg0 =$arg0;
    }
    public function setArg1($arg1)
    {
        $this->arg1 =$arg1;
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

class PropertyInjectionClass extends EntityAbstract
{
    protected $arg0;
    protected $arg1;
}

class DefaultValueClass
{
    const VALUE100 = 100;
    protected $arg0;
    protected $arg1;
    protected $arg2;

    public function __construct(Param0Interface $arg0=null,$arg1=self::VALUE100)
    {
        $this->arg0 = $arg0;
        $this->arg1 = $arg1;
    }
    public function setArg2(Param0Interface $arg2=null)
    {
        $this->arg2 = $arg2;
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

class FieldAnnotationInjection extends EntityAbstract
{
    /**
    * @Inject({@Named("AnerisTest\DiContainerTest\Param0")})
    */
    public $arg0;
}

class PropertyAccessInjection implements PropertyAccessPolicyInterface
{
    /**
    * @Inject({@Named("AnerisTest\DiContainerTest\Param0")})
    */
    public $arg0;
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

/**
* @Scope("prototype")
*/
class PrototypeScope
{
}

/**
* @Scope("singleton")
*/
class SingletonScope
{
}

class PostConstructClass
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

class DiContainerTest extends \PHPUnit_Framework_TestCase
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

    public function testConstructor()
    {
        $di = new Container();

        $cm = $di->getComponentManager();
        $this->assertEquals('Aneris\Container\ComponentDefinitionManager', get_class($cm));
        $dm = $di->getDefinitionManager();
        $this->assertEquals('Aneris\Container\DefinitionManager', get_class($dm));
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage Undefined component.: AnerisTest\DiContainerTest\NonDefinedClass
     */
    public function testGetNone()
    {
        $im = new Container();
        $this->assertFalse($im->has('AnerisTest\DiContainerTest\NonDefinedClass'));
        $i0 = $im->get('AnerisTest\DiContainerTest\NonDefinedClass');
        print_r($i0);
    }

    public function testConstructorArg0()
    {
        $di = new Container();
        $i0 = $di->get('AnerisTest\DiContainerTest\Param0');

        $this->assertEquals('AnerisTest\DiContainerTest\Param0', get_class($i0));
    }

    public function testConstructorArg1()
    {
        $di = new Container();

        $i1 = $di->get('AnerisTest\DiContainerTest\Param1');
        $this->assertEquals('AnerisTest\DiContainerTest\Param1', get_class($i1));

        $i0 = $i1->getArg1();
        $this->assertEquals('AnerisTest\DiContainerTest\Param0', get_class($i0));
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage Undefined a specified class or instance for parameter:AnerisTest\DiContainerTest\Param2::__construct( .. $arg1 .. )
     */
    public function testConstructorArg1NonDef()
    {
        $di = new Container();

        $i1 = $di->get('AnerisTest\DiContainerTest\Param2');
        $this->assertEquals('AnerisTest\DiContainerTest\Param2', get_class($i1));
    }

    public function testConstructorArg1NonDefAddArg()
    {
        $di = new Container();
        $di->getComponentManager()
            ->getComponent('AnerisTest\DiContainerTest\Param2',true)
            ->addConstructorArgWithValue('arg1','xyz');
        $i2 = $di->get('AnerisTest\DiContainerTest\Param2');
        $this->assertEquals('AnerisTest\DiContainerTest\Param2', get_class($i2));

        $this->assertEquals('xyz', $i2->getArg1());
    }

    public function testConstructorArg1NonDefAddArgNullValue()
    {
        $di = new Container();
        $di->getComponentManager()
            ->getComponent('AnerisTest\DiContainerTest\Param2',true)
            ->addConstructorArgWithValue('arg1',null);
        $i2 = $di->get('AnerisTest\DiContainerTest\Param2');
        $this->assertEquals('AnerisTest\DiContainerTest\Param2', get_class($i2));

        $this->assertNull($i2->getArg1());
    }

    public function testConstructorArg2NonDefAddArg()
    {
        $di = new Container();
        $di->getComponentManager()
            ->getComponent('AnerisTest\DiContainerTest\Param2',true)
            ->addConstructorArgWithValue('arg1','xyz');
        $i3 = $di->get('AnerisTest\DiContainerTest\Param3');
        $this->assertEquals('AnerisTest\DiContainerTest\Param3', get_class($i3));

        $i1 = $i3->getArg1();
        $this->assertEquals('AnerisTest\DiContainerTest\Param1', get_class($i1));
        $i2 = $i3->getArg2();
        $this->assertEquals('AnerisTest\DiContainerTest\Param2', get_class($i2));
        $s = $i2->getArg1();
        $this->assertEquals('xyz', $s);
        $i0 = $i1->getArg1();
        $this->assertEquals('AnerisTest\DiContainerTest\Param0', get_class($i0));
    }

    /**
     * @expectedException        Aneris\Container\Exception\InvalidArgumentException
     * @expectedExceptionMessage Class name must be string
     */
    public function testConstructorIlligalName()
    {
        $di = new Container();
        $im = $di->get(array());
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage Undefined component.: AnerisTest\DiContainerTest\Param0Interface
     */
    public function testConstructorArg1UnhandleInterface()
    {
        $di = new Container();
        $i1 = $di->get('AnerisTest\DiContainerTest\Param1Dash');
    }

    public function testConstructorArg1HandleInterfaceWithAlias()
    {
        $di = new Container();
        $di->getComponentManager()
            ->addAlias('AnerisTest\DiContainerTest\Param0Interface', 'AnerisTest\DiContainerTest\Param0');
        $i1 = $di->get('AnerisTest\DiContainerTest\Param1Dash');
        $this->assertEquals('AnerisTest\DiContainerTest\Param1Dash', get_class($i1));
        $this->assertEquals('AnerisTest\DiContainerTest\Param0', get_class($i1->getArg1()));
    }

    public function testConstructorArg1HandleInterfaceWithReference()
    {
        $di = new Container();
        $di->getComponentManager()
            ->getComponent('AnerisTest\DiContainerTest\Param1Dash',true)
            ->addConstructorArgWithReference('arg1','AnerisTest\DiContainerTest\Param0');
        $i1 = $di->get('AnerisTest\DiContainerTest\Param1Dash');
        $this->assertEquals('AnerisTest\DiContainerTest\Param1Dash', get_class($i1));
        $this->assertEquals('AnerisTest\DiContainerTest\Param0', get_class($i1->getArg1()));
    }

    public function testConstructorArg3HandleInterface()
    {
        $di = new Container();
        $di->getComponentManager()
            ->addAlias('AnerisTest\DiContainerTest\Param0Interface', 'AnerisTest\DiContainerTest\Param0');
        $di->getComponentManager()
            ->addAlias('AnerisTest\DiContainerTest\Param1Interface', 'AnerisTest\DiContainerTest\Param1Dash');
        $di->getComponentManager()
            ->getComponent('AnerisTest\DiContainerTest\Param2',true)
            ->addConstructorArgWithValue('arg1','xyz');
        $i3 = $di->get('AnerisTest\DiContainerTest\Param3Dash');
        $this->assertEquals('AnerisTest\DiContainerTest\Param3Dash', get_class($i3));
        $this->assertEquals('AnerisTest\DiContainerTest\Param1Dash', get_class($i3->getArg1()));
        $this->assertEquals('AnerisTest\DiContainerTest\Param2', get_class($i3->getArg2()));
        $this->assertEquals('AnerisTest\DiContainerTest\Param0', get_class($i3->getArg1()->getArg1()));
    }

    public function testLoad()
    {
        /*
        $mgr = new Aneris\Di\DefinitionManager();
        $def = $mgr->getDefinition('AnerisTest\DiContainerTest\Param0');
        $def = $mgr->getDefinition('AnerisTest\DiContainerTest\Param1Dash');
        $def = $mgr->getDefinition('AnerisTest\DiContainerTest\Param2');
        $def = $mgr->getDefinition('AnerisTest\DiContainerTest\Param3Dash');
        $config = $mgr->export();
        echo var_export($config);
        */
        $config = array (
            'runtime_complie' => false,
            'definitions' => array(
                array(
                    'class' => 'AnerisTest\DiContainerTest\Param0',
                    'constructor' => null,
                    'injects' => array(),
                ),
                array(
                    'class' => 'AnerisTest\DiContainerTest\Param1Dash',
                    'constructor' => '__construct',
                    'injects' => array(
                        '__construct' => array(
                            'arg1' => array('ref'=>'AnerisTest\DiContainerTest\Param0Interface'),
                        ),
                    ),
                ),
                array(
                    'class' => 'AnerisTest\DiContainerTest\Param2',
                    'constructor' => '__construct',
                    'injects' => array(
                        '__construct' => array(
                            'arg1' => array(),
                        ),
                    ),
                ),
                array(
                    'class' => 'AnerisTest\DiContainerTest\Param3Dash',
                    'constructor' => '__construct',
                    'injects' => array(
                        '__construct' => array(
                            'arg1' => array('ref'=>'AnerisTest\DiContainerTest\Param1Interface'),
                            'arg2' => array('ref'=>'AnerisTest\DiContainerTest\Param2'),
                        ),
                    ),
                ),
            ),
            'aliases' => array(
                'AnerisTest\DiContainerTest\Param1Interface' => 'AnerisTest\DiContainerTest\Param1Dash',
            ),
            'resources' => array(
                ANERIS_TEST_RESOURCES.'/AcmeTest/Resources/config/service.xml',
            ),
            'components' => array(
                'AnerisTest\DiContainerTest\Param1Dash' => array(
                    'constructor_args' => array(
                        'arg1' => array('ref' => 'AnerisTest\DiContainerTest\Param0'),
                    ),
                ),
                'AnerisTest\DiContainerTest\Param2' => array(
                    'constructor_args' => array(
                        'arg1' => array('value' => 'xyz'),
                    ),
                ),
            ),
        );

        $di = new Container($config);

        $i3 = $di->get('AnerisTest\DiContainerTest\Param3Dash');
        $this->assertEquals('AnerisTest\DiContainerTest\Param3Dash', get_class($i3));
        $this->assertEquals('AnerisTest\DiContainerTest\Param1Dash', get_class($i3->getArg1()));
        $this->assertEquals('AnerisTest\DiContainerTest\Param2', get_class($i3->getArg2()));
        $this->assertEquals('xyz', $i3->getArg2()->getArg1());
        $this->assertEquals('AnerisTest\DiContainerTest\Param0', get_class($i3->getArg1()->getArg1()));
    }

    public function testFactory()
    {
        $config = array (
            'components' => array(
                'AnerisTest\DiContainerTest\CombiService' => array(
                    'factory' => 'AnerisTest\DiContainerTest\CombiServiceFactory::factory',
                ),
            ),
        );
        $di = new Container($config);
        $i0 = $di->get('AnerisTest\DiContainerTest\CombiService');
        $this->assertEquals('AnerisTest\DiContainerTest\CombiService', get_class($i0));
        $data = $i0->getData();
        $this->assertEquals('Created from Factory', $data);
    }

    public function testFactoryWithArgs()
    {
        $config = array (
            'components' => array(
                'TestComponent' => array(
                    'factory' => 'AnerisTest\DiContainerTest\CombiServiceFactory::factory',
                    'factory_args' => array(
                        'a'=>'b',
                    ),
                ),
            ),
        );
        $di = new Container($config);
        $i0 = $di->get('TestComponent');
        $this->assertEquals('AnerisTest\DiContainerTest\CombiService', get_class($i0));
        $this->assertEquals('Created from Factory', $i0->getData());
        $this->assertEquals('TestComponent', $i0->getName());
        $this->assertEquals(array('a'=>'b'), $i0->getArgs());
    }

    public function testGetInstanceByAlias()
    {
        $config = array (
            'aliases' => array(
                'alias0' => 'AnerisTest\DiContainerTest\Param0',
                'instance1' => 'instance',
            ),
        );
        $mgr = new Container($config);
        $this->assertTrue($mgr->has('AnerisTest\DiContainerTest\Param0'));
        $this->assertTrue($mgr->has('alias0'));
        $this->assertEquals('AnerisTest\DiContainerTest\Param0', get_class($mgr->get('alias0')));

        $this->assertFalse($mgr->has('instance1'));
        $this->assertFalse($mgr->has('instance'));
        $mgr->setInstance('instance','xyz');
        $this->assertTrue($mgr->has('instance1'));
        $this->assertTrue($mgr->has('instance'));
        $this->assertEquals('xyz', $mgr->get('instance1'));
    }

    public function testOverloadInstanceByAlias()
    {
        $mgr = new Container();
        $mgr->getComponentManager()->addAlias('test0','test2');
        $mgr->setInstance('test0','instance0');
        $mgr->setInstance('test2','instance2');
        $this->assertEquals('instance2',$mgr->get('test0'));
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage Undefined component.: test0
     */
    public function testGetInstanceByAliasNonDef()
    {
        $mgr = new Container();
        $mgr->getComponentManager()->addAlias('test','test0');
        $i0 = $mgr->get('test');
    }

    public function testHasNormal()
    {
        $im = new Container();
        $this->assertFalse($im->has('test0'));
        $im->setInstance('test0','dummy');
        $this->assertTrue($im->has('test0'));
        $this->assertFalse($im->has('test111'));

        $this->assertFalse($im->has('alias0'));
        $im->getComponentManager()->addAlias('alias0','test0');
        $this->assertTrue($im->has('alias0'));
    }

    public function testHasFactory()
    {
        $config = array(
            'components' => array(
                'test0' => array(
                    'factory'=>'AnerisTest\DiContainerTest\CombiServiceFactory::factory'),
            ),
        );
        $im = new Container();
        $im->setConfig($config);

        $this->assertTrue($im->has('test0'));
        $this->assertFalse($im->has('test111'));

        $this->assertFalse($im->has('alias0'));
        $im->getComponentManager()->addAlias('alias0','test0');
        $this->assertTrue($im->has('alias0'));
    }

    public function testCombinationAliasAndFactory()
    {
        $config = array (
            'aliases' => array(
                'AnerisTest\DiContainerTest\CombiServiceInterface' => 'AnerisTest\DiContainerTest\CombiService',
            ),
            'components' => array(
                'AnerisTest\DiContainerTest\CombiService' => array(
                    'factory' => 'AnerisTest\DiContainerTest\CombiServiceFactory::factory',
                ),
            ),
        );

        $di = new Container($config);
        $i2 = $di->get('AnerisTest\DiContainerTest\CombiDiClass2');
        $this->assertEquals('AnerisTest\DiContainerTest\CombiDiClass2', get_class($i2));

        $i2s0 = $i2->getService0();
        $this->assertEquals('AnerisTest\DiContainerTest\CombiService', get_class($i2s0));
        $i2c1 = $i2->getService1();
        $this->assertEquals('AnerisTest\DiContainerTest\CombiDiClass1', get_class($i2c1));

        $i2c1s0 = $i2c1->getService();
        $this->assertEquals('AnerisTest\DiContainerTest\CombiService', get_class($i2c1s0));

        $data = $i2c1s0->getData();
        $this->assertEquals('Created from Factory', $data);
    }

    public function testCombinationAliasAndFactoryWithoutRuntimeComplile()
    {
        $config = array (
            'runtime_complie' => false,
            'definitions' => array(
                array(
                    'class' => 'AnerisTest\DiContainerTest\CombiDiClass1',
                    'constructor' => '__construct',
                    'injects' => array(
                        '__construct' => array(
                            'service' => array('ref'=>'AnerisTest\DiContainerTest\CombiServiceInterface'),
                        ),
                    ),
                ),
                array(
                    'class' => 'AnerisTest\DiContainerTest\CombiDiClass2',
                    'constructor' => '__construct',
                    'injects' => array(
                        '__construct' => array(
                            'service0' => array('ref'=>'AnerisTest\DiContainerTest\CombiServiceInterface'),
                            'service1' => array('ref'=>'AnerisTest\DiContainerTest\CombiDiClass1'),
                        ),
                    ),
                ),
            ),
            'aliases' => array(
                'AnerisTest\DiContainerTest\CombiServiceInterface' => 'AnerisTest\DiContainerTest\CombiService',
            ),
            'components' => array(
                'AnerisTest\DiContainerTest\CombiService' => array(
                    'factory' => 'AnerisTest\DiContainerTest\CombiServiceFactory::factory',
                ),
            ),
        );

        $di = new Container($config);

        $i2 = $di->get('AnerisTest\DiContainerTest\CombiDiClass2');
        $this->assertEquals('AnerisTest\DiContainerTest\CombiDiClass2', get_class($i2));

        $i2s0 = $i2->getService0();
        $this->assertEquals('AnerisTest\DiContainerTest\CombiService', get_class($i2s0));
        $i2c1 = $i2->getService1();
        $this->assertEquals('AnerisTest\DiContainerTest\CombiDiClass1', get_class($i2c1));

        $i2c1s0 = $i2c1->getService();
        $this->assertEquals('AnerisTest\DiContainerTest\CombiService', get_class($i2c1s0));

        $data = $i2c1s0->getData();
        $this->assertEquals('Created from Factory', $data);
    }

    public function testSetterInjection()
    {
        $config = array(
            'components' => array(
                'AnerisTest\DiContainerTest\SetterInjectionClass' => array(
                    'properties' => array(
                        'arg0' => array('ref'=>'AnerisTest\DiContainerTest\Param0'),
                        'arg1' => array('value'=>'xyz'),
                    ),
                ),
            ),
        );
        $di = new Container($config);
        $i = $di->get('AnerisTest\DiContainerTest\SetterInjectionClass');
        $this->assertEquals('AnerisTest\DiContainerTest\Param0',get_class($i->getArg0()));
        $this->assertEquals('xyz',$i->getArg1());
    }

    public function testPropertyInjection()
    {
        $config = array(
            'components' => array(
                'AnerisTest\DiContainerTest\PropertyInjectionClass' => array(
                    'properties' => array(
                        'arg0' => array('ref'=>'AnerisTest\DiContainerTest\Param0'),
                        'arg1' => array('value'=>'xyz'),
                    ),
                ),
            ),
        );
        $di = new Container($config);
        $i = $di->get('AnerisTest\DiContainerTest\PropertyInjectionClass');
        $this->assertEquals('AnerisTest\DiContainerTest\Param0',get_class($i->getArg0()));
        $this->assertEquals('xyz',$i->getArg1());
    }

    public function testDefaultValue()
    {
        $di = new Container();
        $i = $di->get('AnerisTest\DiContainerTest\DefaultValueClass');
        $this->assertEquals(null,$i->getArg0());
        $this->assertEquals(DefaultValueClass::VALUE100,$i->getArg1());

        $config = array(
            'components' => array(
                'AnerisTest\DiContainerTest\DefaultValueClass' => array(
                    'constructor_args' => array(
                        'arg0' => array('ref'=>'AnerisTest\DiContainerTest\Param0'),
                        'arg1' => array('value'=>123),
                    ),
                ),
            ),
        );
        $di = new Container($config);
        $i = $di->get('AnerisTest\DiContainerTest\DefaultValueClass');
        $this->assertEquals('AnerisTest\DiContainerTest\Param0',get_class($i->getArg0()));
        $this->assertEquals(123,$i->getArg1());
    }

    public function testCache()
    {
        $diConfig = array (
            'cache_path' => '/di/cache',
        );

        $di = new Container($diConfig);
        $i1 = $di->get('AnerisTest\DiContainerTest\\Param1');
        $this->assertEquals('AnerisTest\DiContainerTest\\Param0', get_class($i1->getArg1()));
        unset($di);

        $di2 = new Container($diConfig);
        $i1 = $di2->get('AnerisTest\DiContainerTest\\Param1');
        $this->assertEquals('AnerisTest\DiContainerTest\\Param0', get_class($i1->getArg1()));
    }
    public function testFieldAnnotationInjection()
    {
        $diConfig = array (
            'annotation_manager' => true,
        );
        $di = new Container($diConfig);
        $model1 = $di->get('AnerisTest\DiContainerTest\\FieldAnnotationInjection');
        $arg0 = $model1->getArg0();
        $this->assertEquals('AnerisTest\DiContainerTest\\Param0',get_class($arg0));
    }
    public function testPropertyAccessInjection()
    {
        $diConfig = array (
            'annotation_manager' => true,
        );
        $di = new Container($diConfig);
        $model1 = $di->get('AnerisTest\DiContainerTest\\PropertyAccessInjection');
        $arg0 = $model1->arg0;
        $this->assertEquals('AnerisTest\DiContainerTest\\Param0',get_class($arg0));
    }
    public function testMultiArgInjection()
    {
        $diConfig = array (
            'annotation_manager' => true,
        );
        $di = new Container($diConfig);
        $model1 = $di->get('AnerisTest\DiContainerTest\\MultiArgInjection');
        $this->assertEquals('AnerisTest\DiContainerTest\\Param0',get_class($model1->getArg0()));
        $this->assertEquals('AnerisTest\DiContainerTest\\Param1',get_class($model1->getArg1()));
    }

    public function testAnnotationComponent()
    {
        $diConfig = array (
            'annotation_manager' => true,
            'component_paths' => array(
                ANERIS_TEST_RESOURCES.'/AcmeTest/DiContainer/Component' => true,
            ),
        );
        $di = new Container($diConfig);
        $di->scanComponents();
        $cm = $di->getComponentManager();
        $this->assertEquals('AcmeTest\DiContainer\Component\Model0',$cm->getNamedComponent('model0'));
        $this->assertEquals('AcmeTest\DiContainer\Component\Model1',$cm->getNamedComponent('model1'));
        $this->assertEquals('AcmeTest\DiContainer\Component\Model2',$cm->getNamedComponent('model2'));

        $model2 = $di->get('model2');
        $this->assertEquals('AcmeTest\DiContainer\Component\Model2',get_class($model2));
        $model1 = $model2->getModel1();
        $this->assertEquals('AcmeTest\DiContainer\Component\Model1',get_class($model1));
        $model0 = $model1->getModel0();
        $this->assertEquals('AcmeTest\DiContainer\Component\Model0',get_class($model0));
    }

    public function testParent()
    {
        $config = array(
            'aliases' => array(
                'parent' => 'AnerisTest\DiContainerTest\Param0',
                'test'   => 'AnerisTest\DiContainerTest\Param0',
            ),
        );
        $parent = new Container($config);
        $config = array(
            'aliases' => array(
                'child'  => 'AnerisTest\DiContainerTest\Param1',
                'test'   => 'AnerisTest\DiContainerTest\Param1',
            ),
        );
        $child = new Container($config);
        $child->setParentManager($parent);

        $this->assertFalse($parent->has('child'));
        $this->assertTrue($parent->has('parent'));
        $this->assertTrue($parent->has('test'));

        $this->assertTrue($child->has('child'));
        $this->assertTrue($child->has('parent'));
        $this->assertTrue($child->has('test'));

        $this->assertEquals('AnerisTest\DiContainerTest\Param0',get_class($parent->get('parent')));
        $this->assertEquals('AnerisTest\DiContainerTest\Param0',get_class($parent->get('test')));

        $this->assertEquals('AnerisTest\DiContainerTest\Param1',get_class($child->get('child')));
        $this->assertEquals('AnerisTest\DiContainerTest\Param0',get_class($child->get('parent')));
        $this->assertEquals('AnerisTest\DiContainerTest\Param1',get_class($child->get('test')));
    }

    public function testSingleton()
    {
        $config = array (
            'components' => array(
                'AnerisTest\DiContainerTest\CombiService' => array(
                    'factory' => 'AnerisTest\DiContainerTest\CombiServiceFactory::factory',
                ),
            ),
        );
        $di = new Container($config);
        $i0 = $di->get('AnerisTest\DiContainerTest\Param0');
        $i1 = $di->get('AnerisTest\DiContainerTest\Param0');
        $this->assertEquals(spl_object_hash($i0),spl_object_hash($i1));

        $i0 = $di->get('AnerisTest\DiContainerTest\CombiService');
        $i1 = $di->get('AnerisTest\DiContainerTest\CombiService');
        $this->assertEquals(spl_object_hash($i0),spl_object_hash($i1));
    }

    public function testNamedComponentOnConfig()
    {
        $config = array (
            'components' => array(
                'CombiService' => array(
                    'factory' => 'AnerisTest\DiContainerTest\CombiServiceFactory::factory',
                ),
                'CombiDiClass1' => array(
                    'class' => 'AnerisTest\DiContainerTest\CombiDiClass1',
                    'constructor_args' => array(
                        'service' => array('ref'=>'CombiService'),
                    ),
                ),
                'CombiDiClass2' => array(
                    'class' => 'AnerisTest\DiContainerTest\CombiDiClass2',
                    'constructor_args' => array(
                        'service0' => array('ref'=>'CombiService'),
                        'service1' => array('ref'=>'CombiDiClass1'),
                    ),
                ),
                'Param2WithValue1' => array(
                    'class' => 'AnerisTest\DiContainerTest\Param2',
                    'constructor_args' => array(
                        'arg1' => array('value'=>'value1'),
                    ),
                ),
                'Param2WithValue2' => array(
                    'class' => 'AnerisTest\DiContainerTest\Param2',
                    'constructor_args' => array(
                        'arg1' => array('value'=>'value2'),
                    ),
                ),
            ),
        );
        $di = new Container($config);
        $i2 = $di->get('CombiDiClass2');
        $this->assertEquals('AnerisTest\DiContainerTest\CombiDiClass2',get_class($i2));
        $this->assertEquals('AnerisTest\DiContainerTest\CombiService',get_class($i2->getService0()));
        $this->assertEquals('AnerisTest\DiContainerTest\CombiDiClass1',get_class($i2->getService1()));
        $this->assertEquals('AnerisTest\DiContainerTest\CombiService',get_class($i2->getService1()->getService()));

        $v1 = $di->get('Param2WithValue1');
        $v2 = $di->get('Param2WithValue2');
        $this->assertEquals('value1',$v1->getArg1());
        $this->assertEquals('value2',$v2->getArg1());
    }

    public function testScope()
    {
        $config = array (
            'components' => array(
                'Prototype' => array(
                    'class' => 'AnerisTest\DiContainerTest\Param0',
                    'scope' => 'prototype',
                ),
                'Singleton' => array(
                    'class' => 'AnerisTest\DiContainerTest\Param0',
                    'scope' => 'singleton',
                ),
                'PrototypeFactory' => array(
                    'factory' => 'AnerisTest\DiContainerTest\CombiServiceFactory::factory',
                    'scope' => 'prototype',
                ),
                'SingletonFactory' => array(
                    'factory' => 'AnerisTest\DiContainerTest\CombiServiceFactory::factory',
                    'scope' => 'singleton',
                ),
            ),
        );
        $di = new Container($config);
        $i0 = $di->get('Prototype');
        $i1 = $di->get('Prototype');
        $this->assertNotEquals(spl_object_hash($i0),spl_object_hash($i1));

        $i0 = $di->get('Singleton');
        $i1 = $di->get('Singleton');
        $this->assertEquals(spl_object_hash($i0),spl_object_hash($i1));

        $i0 = $di->get('PrototypeFactory');
        $i1 = $di->get('PrototypeFactory');
        $this->assertNotEquals(spl_object_hash($i0),spl_object_hash($i1));

        $i0 = $di->get('SingletonFactory');
        $i1 = $di->get('SingletonFactory');
        $this->assertEquals(spl_object_hash($i0),spl_object_hash($i1));
    }

    public function testScopeAnnotation()
    {
        $config = array (
            'annotation_manager' => true,
        );
        $di = new Container($config);
        $i0 = $di->get('AnerisTest\DiContainerTest\PrototypeScope');
        $i1 = $di->get('AnerisTest\DiContainerTest\PrototypeScope');
        $this->assertNotEquals(spl_object_hash($i0),spl_object_hash($i1));

        $i0 = $di->get('AnerisTest\DiContainerTest\SingletonScope');
        $i1 = $di->get('AnerisTest\DiContainerTest\SingletonScope');
        $this->assertEquals(spl_object_hash($i0),spl_object_hash($i1));
    }

    public function testPostConstructClass()
    {
        $config = array (
            'annotation_manager' => false,
            'components' => array(
                'AnerisTest\DiContainerTest\PostConstructClass' => array(
                    'properties' => array(
                        'arg0' => array('value' => 'xyz'),
                    ),
                    'init_method' => 'init',
                ),
            ),
        );
        $di = new Container($config);
        $i0 = $di->get('AnerisTest\DiContainerTest\PostConstructClass');
        $this->assertEquals('xyz',$i0->getArg0());
        $this->assertEquals(true,$i0->is_initialized());
    }

    public function testPostConstructClassByAnnotation()
    {
        $config = array (
            'annotation_manager' => false,
        );
        $di = new Container($config);
        $i0 = $di->get('AnerisTest\DiContainerTest\PostConstructClass');
        $this->assertEquals(null,$i0->getArg0());
        $this->assertEquals(null,$i0->is_initialized());

        \Aneris\Stdlib\Cache\CacheFactory::clearCache();

        $config = array (
            'annotation_manager' => true,
        );
        $di = new Container($config);
        $i0 = $di->get('AnerisTest\DiContainerTest\PostConstructClass');
        $this->assertEquals(123,$i0->getArg0());
        $this->assertEquals(true,$i0->is_initialized());
    }
    public function testConfigInjection()
    {
        $config = array(
            'components' => array(
                'AnerisTest\DiContainerTest\Param2' => array(
                    'constructor_args' => array(
                        'arg1' => array('ref'=>'translator::language'),
                    ),
                ),
                'translator::language' => array(
                    'factory' => 'Aneris\Container\ConfigurationFactory::factory',
                ),
            ),
        );
        $di = new Container($config);
        $config = array(
            'translator' => array(
                'language' => 'ja_JP',
            ),
        );
        $di->setInstance('config',$config);
        $i = $di->get('AnerisTest\DiContainerTest\Param2');
        $this->assertEquals('ja_JP',$i->getArg1());
    }
    public function testConfigInjection2()
    {
        $config = array(
            'components' => array(
                'AnerisTest\DiContainerTest\Param2' => array(
                    'constructor_args' => array(
                        'arg1' => array('ref'=>'translator_language'),
                    ),
                ),
                'translator_language' => array(
                    'factory' => 'Aneris\Container\ConfigurationFactory::factory',
                    'factory_args' => array('config'=>'translator::language'),
                ),
            ),
        );
        $di = new Container($config);
        $config = array(
            'translator' => array(
                'language' => 'ja_JP',
            ),
        );
        $di->setInstance('config',$config);
        $i = $di->get('AnerisTest\DiContainerTest\Param2');
        $this->assertEquals('ja_JP',$i->getArg1());
    }
    public function testConfigInjection3()
    {
        $config = array(
            'components' => array(
                'AnerisTest\DiContainerTest\Param2' => array(
                    'constructor_args' => array(
                        'arg1' => array('ref'=>'translator_language'),
                    ),
                ),
                'translator_language' => array(
                    'factory' => 'Aneris\Container\ConfigurationFactory::factory',
                    'factory_args' => array('config'=>'translator'),
                ),
            ),
        );
        $di = new Container($config);
        $config = array(
            'translator' => array(
                'language' => 'ja_JP',
            ),
        );
        $di->setInstance('config',$config);
        $i = $di->get('AnerisTest\DiContainerTest\Param2');
        $this->assertEquals(array('language' => 'ja_JP'),$i->getArg1());
    }
}
