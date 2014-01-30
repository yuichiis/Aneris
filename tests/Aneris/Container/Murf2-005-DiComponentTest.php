<?php
namespace AnerisTest\DiComponentTest;

use Aneris\Annotation\AnnotationManager;

// Test Target Classes
use Aneris\Container\ComponentDefinition;

class DiComponentTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \Aneris\Stdlib\Cache\CacheFactory::clearCache();
    }
    public function testCombination()
    {
        $config = array(
            'name' => 'AnerisTest\DiComponentTest\Test1Component',
            'class' => 'AnerisTest\DiComponentTest\Test1',
            'constructor_args' => array(
                'arg0' => array('ref'  =>'AnerisTest\DiComponentTest\Test0'),
                'arg1' => array('value'=>'value1'),
            ),
            'properties' => array(
                'prop0' => array('ref'   =>'AnerisTest\DiComponentTest\Test0'),
                'prop1' => array('value' =>'value1'),
            ),
            'injects' => array(
                'setter1' => array(
                    'arg10' => array('ref'  => 'AnerisTest\DiComponentTest\Test10'),
                    'arg11' => array('value'=>'value11'),
                ),
            ),
            'factory' => 'AnerisTest\DiComponentTest\Test0Factory::factory',
            'factory_args' => array('foo'=>'bar'),
            'init_method' => 'init',
            'scope' => 'prototype',
            'lazy' => true,
        );
        $component = new ComponentDefinition($config);
        $injects = array(
            'setter1' => array(
                'arg10' => array('ref'  => 'AnerisTest\DiComponentTest\Test10'),
                'arg11' => array('value'=>'value11'),
            ),
            '__construct' => array(
                'arg0' => array('ref'  =>'AnerisTest\DiComponentTest\Test0'),
                'arg1' => array('value'=>'value1'),
            ),
            'setProp0' => array(
                'prop0' => array('ref'   =>'AnerisTest\DiComponentTest\Test0'),
            ),
            'setProp1' => array(
                'prop1' => array('value' =>'value1'),
            ),
        );
        $this->assertEquals($injects,$component->getInjects());

        $inject = array(
            'arg0' => array('ref'  =>'AnerisTest\DiComponentTest\Test0'),
            'arg1' => array('value'=>'value1'),
        );
        $this->assertEquals($inject,$component->getInject('__construct'));
        $this->assertFalse($component->getInject('nonesetter'));
        
        $this->assertTrue($component->hasFactory());
        $this->assertEquals('AnerisTest\DiComponentTest\Test0Factory::factory',$component->getFactory());
        $this->assertFalse($component->hasClosureFactory());

        $this->assertEquals(array('foo'=>'bar'),$component->getFactoryArgs());
        $this->assertEquals('AnerisTest\DiComponentTest\Test1Component',$component->getName());
        $this->assertEquals('AnerisTest\DiComponentTest\Test1',$component->getClassName());
        $this->assertEquals('init',$component->getInitMethod());
        $this->assertEquals('prototype',$component->getScope());
        $this->assertEquals(true,$component->isLazy());
    }

    public function testAddProperty()
    {
        $component = new ComponentDefinition();
        $component->addPropertyWithReference('arg1','Component1');
        $injects = array(
            'setArg1' => array(
                'arg1' => array('ref'  =>'Component1'),
            ),
        );
        $this->assertEquals($injects,$component->getInjects());

        $component->addPropertyWithValue('arg1','value1');
        $injects = array(
            'setArg1' => array(
                'arg1' => array('value'=>'value1'),
            ),
        );
        $this->assertEquals($injects,$component->getInjects());

        $component->addPropertyWithValue('arg2','value2');
        $injects = array(
            'setArg1' => array(
                'arg1' => array('value'=>'value1'),
            ),
            'setArg2' => array(
                'arg2' => array('value'=>'value2'),
            ),
        );
        $this->assertEquals($injects,$component->getInjects());

        $component->addPropertyWithReference('arg2','Component2');
        $injects = array(
            'setArg1' => array(
                'arg1' => array('value'=>'value1'),
            ),
            'setArg2' => array(
                'arg2' => array('ref'  =>'Component2'),
            ),
        );
        $this->assertEquals($injects,$component->getInjects());
    }
}
