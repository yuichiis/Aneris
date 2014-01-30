<?php
namespace AnerisTest\DiComponentManagerTest;

use Aneris\Annotation\AnnotationManager;

// Test Target Classes
use Aneris\Container\ComponentDefinitionManager;
use Aneris\Container\ComponentScanner;

class DiComponentManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \Aneris\Stdlib\Cache\CacheFactory::clearCache();
    }

    public function testCache()
    {
    }

    public function testNamedComponentWithCache()
    {
        $mgr = new ComponentDefinitionManager();
        $mgr->setEnableCache(true);
        $mgr->setConfig(array());
        $componentScanner = new ComponentScanner();
        $componentScanner->setAnnotationManager(AnnotationManager::factory());
        $mgr->attachScanner($componentScanner);
        $componentScanner->scan(array(ANERIS_TEST_RESOURCES.'/AcmeTest/DiContainer/Component'=>true));
        $this->assertEquals('AcmeTest\DiContainer\Component\Model0',$mgr->getNamedComponent('model0'));
        $this->assertEquals('AcmeTest\DiContainer\Component\Model1',$mgr->getNamedComponent('model1'));
        $this->assertEquals('AcmeTest\DiContainer\Component\Model2',$mgr->getNamedComponent('model2'));
    }

    public function testNamedComponentWithOutCache()
    {
        $mgr = new ComponentDefinitionManager();
        $mgr->setEnableCache(false);
        $mgr->setConfig(array());
        $componentScanner = new ComponentScanner();
        $componentScanner->setAnnotationManager(AnnotationManager::factory());
        $mgr->attachScanner($componentScanner);
        $componentScanner->scan(array(ANERIS_TEST_RESOURCES.'/AcmeTest/DiContainer/Component'=>true));
        $this->assertEquals('AcmeTest\DiContainer\Component\Model0',$mgr->getNamedComponent('model0'));
        $this->assertEquals('AcmeTest\DiContainer\Component\Model1',$mgr->getNamedComponent('model1'));
        $this->assertEquals('AcmeTest\DiContainer\Component\Model2',$mgr->getNamedComponent('model2'));
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage directory not found:
     */
    public function testInvalidScanDirectory()
    {
        $mgr = new ComponentDefinitionManager();
        $mgr->setEnableCache(false);
        $mgr->setConfig(array());
        $componentScanner = new ComponentScanner();
        $componentScanner->setAnnotationManager(AnnotationManager::factory());
        $mgr->attachScanner($componentScanner);
        $componentScanner->scan(array(ANERIS_TEST_RESOURCES.'/AcmeTest/DiContainer/Non'=>true));
    }

    public function testConfig()
    {
        $config = array(
            'components' => array(
                'AnerisTest\DiComponentManagerTest\Test0' => array(
                    'factory' => 'AnerisTest\DiComponentManagerTest\Test0::factory',
                ),
                'AnerisTest\DiComponentManagerTest\Test1' => array(
                ),
                'test1' => array(
                    'class' => 'AnerisTest\DiComponentManagerTest\Test1',
                    'constructor_args' => array(
                        'arg0' => array('ref'  =>'AnerisTest\DiComponentTest\Test0'),
                        'arg1' => array('value'=>'value1'),
                    ),
                ),
            ),
        );
        $mgr = new ComponentDefinitionManager();
        $mgr->setConfig($config);

        $component = $mgr->getComponent('AnerisTest\DiComponentManagerTest\Test0');
        $this->assertEquals(array(),$component->getInjects());
        $this->assertEquals(false,$component->getInject('__construct'));
        $this->assertEquals(true,$component->hasFactory());
        $this->assertEquals('AnerisTest\DiComponentManagerTest\Test0::factory',$component->getFactory());
        $this->assertEquals('AnerisTest\DiComponentManagerTest\Test0',$component->getName());
        $this->assertEquals(null,$component->getClassName());
        
        $component = $mgr->getComponent('AnerisTest\DiComponentManagerTest\Test1');
        $this->assertEquals(array(),$component->getInjects());
        $this->assertEquals(false,$component->getInject('__construct'));
        $this->assertEquals(false,$component->hasFactory());
        $this->assertEquals(null,$component->getFactory());
        $this->assertEquals('AnerisTest\DiComponentManagerTest\Test1',$component->getName());
        $this->assertEquals('AnerisTest\DiComponentManagerTest\Test1',$component->getClassName());

        $injects = array(
            '__construct' => array(
                'arg0' => array('ref'  =>'AnerisTest\DiComponentTest\Test0'),
                'arg1' => array('value'=>'value1'),
            ),
        );
        $construct = array(
            'arg0' => array('ref'  =>'AnerisTest\DiComponentTest\Test0'),
            'arg1' => array('value'=>'value1'),
        );
        $component = $mgr->getComponent('test1');
        $this->assertEquals('AnerisTest\DiComponentManagerTest\Test1',$component->getClassName());
        $this->assertEquals('test1',$component->getName());
        $this->assertEquals($injects,$component->getInjects());
        $this->assertEquals($construct,$component->getInject('__construct'));
        $this->assertEquals(false,$component->getInject('none'));
        $this->assertEquals(false,$component->hasFactory());
        $this->assertEquals(null,$component->getFactory());

        $this->assertFalse($mgr->getComponent('TestComponent'));
        $component = $mgr->getComponent('TestComponent',true);
        $this->assertEquals('Aneris\Container\ComponentDefinition',get_class($component));
        $this->assertEquals('TestComponent',$component->getClassName());
        $this->assertEquals('TestComponent',$component->getName());
        $this->assertEquals(array(),$component->getInjects());
        $this->assertEquals(null,$component->getFactory());
    }

    public function testGetNew()
    {
        $mgr = new ComponentDefinitionManager();
        $component = $mgr->getComponent('test',true);
        $component->addPropertyWithValue('var1','value1');

        $component2 = $mgr->getComponent('test');
        $injects = array(
            'setVar1' => array(
                'var1' => array('value'=>'value1'),
            ),
        );
        $this->assertEquals($injects,$component2->getInjects());
    }

    public function testHasComponent()
    {
        $mgr = new ComponentDefinitionManager();
        $this->assertFalse($mgr->hasComponent('test'));
        $component = $mgr->getComponent('test',true);
        $this->assertTrue($mgr->hasComponent('test'));
    }

    public function testAddAlias()
    {
        $mgr = new ComponentDefinitionManager();
        $alias = 'Alias';
        $className = 'AnerisTest\ServiceManagerTest\CreateInstance0';
        $mgr->addAlias($alias,$className);
        $this->assertEquals($className,$mgr->resolveAlias($alias));
    }

    public function testgetAliasNonDef()
    {
        $mgr = new ComponentDefinitionManager();
        $alias = 'NonDef';
        $this->assertEquals($alias, $mgr->resolveAlias($alias));
    }
}
