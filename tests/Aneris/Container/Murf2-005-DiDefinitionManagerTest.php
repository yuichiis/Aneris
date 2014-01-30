<?php
namespace AnerisTest\DiDefinitionManagerTest;

use Aneris\Annotation\AnnotationManager;

// Test Target Classes
use Aneris\Container\DefinitionManager;
use Aneris\Container\Annotations\Inject;
use Aneris\Container\Annotations\Named;


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
    public function __construct(Param1 $arg1, Param2 $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}

/**
* @Named("named0")
*/
class NamedInjection
{
    /**
    * @Inject({@Named(parameter="arg1",value="AnerisTest\DiDefinitionManagerTest\Param0")})
    */
    public function __construct($arg1)
    {
        $this->arg1 = $arg1;
    }
    public function getArg1()
    {
        return $this->arg1;
    }
}
class DiDefinitionManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testNoConstructor()
    {
        $mgr = new DefinitionManager();
        $mgr->setEnableCache(false);
        $def = $mgr->getDefinition('AnerisTest\DiDefinitionManagerTest\Param0');

        $this->assertNull($def->getConstructor());

        $injects = $def->getInjects();

        $this->assertTrue(is_array($injects));
        $this->assertEquals(0, count($injects));
    }

    public function testConstructorArg1()
    {
        $mgr = new DefinitionManager();
        $mgr->setEnableCache(false);
        $def = $mgr->getDefinition('AnerisTest\DiDefinitionManagerTest\Param1');

        $this->assertEquals('__construct', $def->getConstructor());

        $injects = $def->getInjects();

        $this->assertTrue(is_array($injects));
        $this->assertEquals(1, count($injects));
        $this->assertEquals(1, count($injects['__construct']));
        $this->assertTrue(array_key_exists('arg1',$injects['__construct']));
        $this->assertEquals('AnerisTest\DiDefinitionManagerTest\Param0', $injects['__construct']['arg1']['ref']);
    }

    public function testConstructorArg1NonDef()
    {
        $mgr = new DefinitionManager();
        $mgr->setEnableCache(false);
        $def = $mgr->getDefinition('AnerisTest\DiDefinitionManagerTest\Param2');

        $this->assertEquals('__construct', $def->getConstructor());

        $injects = $def->getInjects();

        $this->assertTrue(is_array($injects));
        $this->assertEquals(1, count($injects['__construct']));
        $this->assertTrue(array_key_exists('arg1',$injects['__construct']));
        $this->assertEquals(array(), $injects['__construct']['arg1']);
    }

    public function testConstructorArg2()
    {
        $mgr = new DefinitionManager();
        $mgr->setEnableCache(false);
        $def = $mgr->getDefinition('AnerisTest\DiDefinitionManagerTest\Param3');

        $this->assertEquals('__construct', $def->getConstructor());

        $injects = $def->getInjects();

        $this->assertTrue(is_array($injects));
        $this->assertEquals(2, count($injects['__construct']));
        $this->assertTrue(array_key_exists('arg1',$injects['__construct']));
        $this->assertEquals('AnerisTest\DiDefinitionManagerTest\Param1', $injects['__construct']['arg1']['ref']);
        $this->assertTrue(array_key_exists('arg2',$injects['__construct']));
        $this->assertEquals('AnerisTest\DiDefinitionManagerTest\Param2', $injects['__construct']['arg2']['ref']);
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage AnerisTest\DiDefinitionManagerTest\NotExist does not exist
     */
    public function testNotExist()
    {
        $mgr = new DefinitionManager();
        $mgr->setEnableCache(false);
        $def = $mgr->getDefinition('AnerisTest\DiDefinitionManagerTest\NotExist');
        $a = $def->getConstructor();
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage AnerisTest\DiDefinitionManagerTest\NotExist does not defined
     */
    public function testNotDefined()
    {
        $mgr = new DefinitionManager();
        $mgr->setEnableCache(false);
        $mgr->setRuntimeComplie(false);
        $def = $mgr->getDefinition('AnerisTest\DiDefinitionManagerTest\NotExist');
        $a = $def->getConstructor();
    }

    public function testNamedInjection()
    {
        $mgr = new DefinitionManager();
        $mgr->setAnnotationManager(AnnotationManager::factory());
        $mgr->setEnableCache(false);
        $def = $mgr->getDefinition('AnerisTest\DiDefinitionManagerTest\NamedInjection');
        $exp = array(
            'class' => 'AnerisTest\DiDefinitionManagerTest\\NamedInjection',
            'name' => 'named0',
            'constructor' => '__construct',
            'injects' => array (
                '__construct' => array (
                    'arg1' => array('ref'=>'AnerisTest\DiDefinitionManagerTest\\Param0'),
                ),
            ),
        );
        $this->assertEquals($exp,$def->export());
    }
    ///**
    // * @expectedException        Aneris\Container\Exception\DomainException
    // * @expectedExceptionMessage Aneris2DiDefinitionMgrTestParam0 is already defined
    // */
    //public function testDuplicate()
    //{
    //    $def = new Aneris\Container\Definition('Aneris2DiDefinitionMgrTestParam0');
    //    $mgr = new Aneris\Container\DefinitionManager();
    //    $mgr->addDefinition('Aneris2DiDefinitionMgrTestParam0',$def);
    //    $mgr->addDefinition('Aneris2DiDefinitionMgrTestParam0',$def);
    //}

    ///public function testExpoertAndLoad()
    //{
    //    $mgr = new Aneris\Container\DefinitionManager();
    //    $def = $mgr->getDefinition('Aneris2DiDefinitionMgrTestParam0');
    //    $def = $mgr->getDefinition('Aneris2DiDefinitionMgrTestParam1');
    //    $def = $mgr->getDefinition('Aneris2DiDefinitionMgrTestParam2');
    //    $def = $mgr->getDefinition('Aneris2DiDefinitionMgrTestParam3');

    //    $config = $mgr->export();
    //    //echo var_export($config);

    //    $mgr = new Aneris\Container\DefinitionManager();
    //    $mgr->import($config);

    //}
}
