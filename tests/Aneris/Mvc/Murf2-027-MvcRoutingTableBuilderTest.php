<?php
namespace MvcRoutingTableBuilderTest;

// Test Target Classes
use Aneris\Mvc\Annotations\Controller;
use Aneris\Mvc\Annotations\RequestMapping;
use Aneris\Mvc\RoutingTableBuilder\Annotation as RoutingTableBuilderAnnotation;
use Aneris\Mvc\RoutingTableBuilder\File as RoutingTableBuilderFile;

/**
 * @Controller
 * @RequestMapping(value="/ctl",ns="controllernamespace")
 */
class FooControllerClass
{
    /**
      * @var string  dummy for test
      */
    protected $test;
    /**
     * @return null
     *
     * @RequestMapping(
     *      value="/act",method="POST",headers={Accept="text/html"},
     *      ns="methodnamespace",parameters={"id","mode"},
     *      view="foo/act",name="act"
     * )
     */
    public function actmethod()
    {
        return;
    }
    /**
     * @RequestMapping(
     *      value="/act2"
     * )
     */
    public function actmethod2()
    {
        return;
    }
    public function normalMethod()
    {
        return;
    }
    /**
     * @RequestMapping(
     *      value="/"
     * )
     */
    public function indexAction()
    {
        return;
    }
}
/**
 * @Controller
 */
class Foo2ControllerClass
{
    /**
     * @RequestMapping(
     *      value="/act",method="GET"
     * )
     */
    public function actmethod()
    {
        return;
    }
}
/**
 * @RequestMapping(value="/ctl",ns="controllernamespace")
 */
class Foo3NoneControllerClass
{
    /**
     * @RequestMapping(
     *      value="/act",method="GET"
     * )
     */
    public function actmethod()
    {
        return;
    }
}
/**
 * @Controller
 * @RequestMapping(value="/ctl",ns="controllernamespace")
 */
class Foo4NoneActionClass
{
    public function actmethod()
    {
        return;
    }
}
/**
 * @Controller
 * @RequestMapping(value="/ctl")
 */
class Foo5NonePathClass
{
    /**
     * @RequestMapping(
     *      method="GET"
     * )
     */
    public function actmethod()
    {
        return;
    }
}

class TestLoader
{
    static public function load($file)
    {
        return yaml_parse_file($file);
    }
}

class MvcRoutingTableBuilderTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        //$loader = Aneris\Loader\Autoloader::factory();
        //$loader->setNameSpace('AcmeTest',ANERIS_TEST_RESOURCES.'/AcmeTest');
    }
    public static function tearDownAfterClass()
    {
    }

    public function testNormal()
    {
        $result = array(
            'methodnamespace\act' => array(
                'path' => '/ctl/act',
                'conditions' => array(
                    'method' => 'POST',
                    'headers' => array(
                        'Accept' => 'text/html',
                    ),
                ),
                'defaults' => array(
                    'namespace' => 'methodnamespace',
                    'class' => 'MvcRoutingTableBuilderTest\\FooControllerClass',
                    'method' => 'actmethod',
                    'view' => 'foo/act',
                ),
                'type' => 'segment',
                'options' => array(
                    'parameters' => array('id','mode')
                ),
            ),

            'MvcRoutingTableBuilderTest\\FooControllerClass::actmethod2' => array(
                'path' => '/ctl/act2',
                'defaults' => array(
                    'namespace' => 'controllernamespace',
                    'class' => 'MvcRoutingTableBuilderTest\\FooControllerClass',
                    'method' => 'actmethod2',
                ),
                'type' => 'literal',
            ),
            'MvcRoutingTableBuilderTest\\FooControllerClass::indexAction' => array(
                'path' => '/ctl',
                'defaults' => array(
                    'namespace' => 'controllernamespace',
                    'class' => 'MvcRoutingTableBuilderTest\\FooControllerClass',
                    'method' => 'indexAction',
                ),
                'type' => 'literal',
            ),
        );

        $builder = new RoutingTableBuilderAnnotation();
        $this->assertEquals($result,$builder->parseControllerClass('MvcRoutingTableBuilderTest\FooControllerClass')->getRoutes());

        $result = array(
            'MvcRoutingTableBuilderTest\\Foo2ControllerClass::actmethod' => array(
                'path' => '/act',
                'conditions' => array(
                    'method' => 'GET',
                ),
                'defaults' => array(
                    'namespace' => 'MvcRoutingTableBuilderTest',
                    'class' => 'MvcRoutingTableBuilderTest\\Foo2ControllerClass',
                    'method' => 'actmethod',
                ),
                'type' => 'literal',
            ),
        );

        $builder = new RoutingTableBuilderAnnotation();
        $this->assertEquals($result,$builder->parseControllerClass('MvcRoutingTableBuilderTest\Foo2ControllerClass')->getRoutes());

        $result = array(
        );
        $builder = new RoutingTableBuilderAnnotation();
        $this->assertEquals($result,$builder->parseControllerClass('MvcRoutingTableBuilderTest\Foo3NoneControllerClass')->getRoutes());
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage there is no action-method in a controller.: 
     */
    public function testNoActionMethodInController()
    {
        $result = array(
        );
        $builder = new RoutingTableBuilderAnnotation();
        $this->assertEquals($result,$builder->parseControllerClass('MvcRoutingTableBuilderTest\Foo4NoneActionClass')->getRoutes());
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage a mapping path is not specified.: 
     */
    public function testNoPathInAction()
    {
        $result = array(
        );
        $builder = new RoutingTableBuilderAnnotation();
        $this->assertEquals($result,$builder->parseControllerClass('MvcRoutingTableBuilderTest\Foo5NonePathClass')->getRoutes());
    }

    public function testFile()
    {
        $result = array(
            'AcmeTest\MvcRouter\TestControllerClass::foo' => array(
                'path' => '/foo',
                'defaults' => array(
                    'namespace' => 'AcmeTest\MvcRouter',
                    'class' => 'AcmeTest\MvcRouter\TestControllerClass',
                    'method' => 'foo',
                ),
                'type' => 'literal',
            ),
        );

        $builder = new RoutingTableBuilderAnnotation();
        $this->assertEquals($result,$builder->parseControllerFile(ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/TestControllerClass.php')->getRoutes());
    }

    public function testFileHasNoClass()
    {
        $result = array(
        );

        $builder = new RoutingTableBuilderAnnotation();
        $this->assertEquals($result,$builder->parseControllerFile(ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/NoClass.php')->getRoutes());
    }
    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage duplicate route name "AcmeTest\MvcRouter\foo":
     */
    public function testDuplicateRouteName()
    {
        $result = array(
        );

        $builder = new RoutingTableBuilderAnnotation();
        $this->assertEquals($result,$builder->parseControllerFile(ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/DuplicateRouteName.php')->getRoutes());
    }

    public function testScanDirectory()
    {
        $config = array(
            'controller_paths' => array(
                ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/Dir1' => true,
                ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/Dir2' => true,
            ),
        );

        $builder = new RoutingTableBuilderAnnotation();
        $builder->setConfig($config);
        $routes = $builder->build()->getRoutes();
        $this->assertEquals(4,count($routes));
        $this->assertTrue(isset($routes['AcmeTest\MvcRouter\Controller\TestControllerClass1::foo']));
        $this->assertTrue(isset($routes['AcmeTest\MvcRouter\Controller\TestControllerClass2::foo']));
        $this->assertTrue(isset($routes['AcmeTest\MvcRouter\Controller\TestControllerClass3::foo']));
        $this->assertTrue(isset($routes['AcmeTest\MvcRouter\Controller\TestControllerClass4::foo']));
    }

    public function testFileConfig()
    {
        $config = array(
            'config_files' => array(
                'php' => array(
                    ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/config/route1.php' => true,
                    ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/config/route2.php' => true,
                ),
            ),
        );
        $builder = new RoutingTableBuilderFile();
        $builder->setConfig($config);
        $routes = $builder->build()->getRoutes();
        $this->assertEquals(3,count($routes));
        $this->assertTrue(isset($routes['AcmTest\MvcRouter\route1-1']));
        $this->assertTrue(isset($routes['AcmTest\MvcRouter\route1-2']));
        $this->assertTrue(isset($routes['AcmTest\MvcRouter\route2']));
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage routing configuration file is not exist.:
     */
    public function testFileConfigNotExist()
    {
        $config = array(
            'config_files' => array(
                'php' => array(
                    ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/config/notexist.php' => true,
                ),
            ),
        );
        $builder = new RoutingTableBuilderFile();
        $builder->setConfig($config);
        $routes = $builder->build()->getRoutes();
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage invalid return type or loading error.:
     */
    public function testFileConfigInvalidFormat()
    {
        $config = array(
            'config_files' => array(
                'php' => array(
                    ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/config/null.php' => true,
                ),
            ),
        );
        $builder = new RoutingTableBuilderFile();
        $builder->setConfig($config);
        $routes = $builder->build()->getRoutes();
    }

    public function testFileConfigYaml()
    {
        if(!extension_loaded('yaml'))
            return;
        $config = array(
            'config_files' => array(
                'yaml' => array(
                    ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/config/routing.yml' => true,
                ),
            ),
        );
        $builder = new RoutingTableBuilderFile();
        $builder->setConfig($config);
        $routes = $builder->build()->getRoutes();
        $this->assertEquals(2,count($routes));
        $this->assertTrue(isset($routes['AcmTest\MvcRouter\home']));
        $this->assertTrue(isset($routes['AcmTest\MvcRouter\foo']));
    }

    public function testFileConfigCustom()
    {
        if(!extension_loaded('yaml'))
            return;
        $config = array(
            'config_files' => array(
                'MvcRoutingTableBuilderTest\\TestLoader::load' => array(
                    ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/config/routing.yml' => true,
                ),
            ),
        );
        $builder = new RoutingTableBuilderFile();
        $builder->setConfig($config);
        $routes = $builder->build()->getRoutes();
        $this->assertEquals(2,count($routes));
        $this->assertTrue(isset($routes['AcmTest\MvcRouter\home']));
        $this->assertTrue(isset($routes['AcmTest\MvcRouter\foo']));
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage a loader is not found.: INVALIDLOADER
     */
    public function testFileConfigInvalidLoader()
    {
        $config = array(
            'config_files' => array(
                'INVALIDLOADER' => array(
                    ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/config/routing.yml' => true,
                ),
            ),
        );
        $builder = new RoutingTableBuilderFile();
        $builder->setConfig($config);
        $routes = $builder->build()->getRoutes();
    }
}
