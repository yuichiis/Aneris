<?php
namespace AnerisTest\ModuleManagerTest;

use Aneris\Loader\AutoLoader;
use stdClass;

// Test Target Classes
use Aneris\Container\ModuleManager;

class ModuleManagerTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        $loader = AutoLoader::factory();
        $loader->add('AcmeTest',ANERIS_TEST_RESOURCES);
    }

    public function setUp()
    {
    }

    public function testConfigNormal()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                ),
            ),
            'global_config' => array(
                'global'    => 'This is global',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $config = $moduleManager->getConfig();
        $this->assertEquals('This is global', $config['global_config']['global']);
        $this->assertEquals('module1', $config['module_setting']['each_setting']['AcmeTest\Module1']);
        $this->assertEquals(array('AcmeTest\Module1'=>'testpath1'), $config['module_setting']['share_setting']['paths']);
    }

    public function testConfigNormalDouble()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
            ),
            'global_config' => array(
                'global'    => 'This is global',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $config = $moduleManager->getConfig();
        $this->assertEquals('This is global', $config['global_config']['global']);
        $this->assertEquals('module1', $config['module_setting']['each_setting']['AcmeTest\Module1']);
        $this->assertEquals('module2', $config['module_setting']['each_setting']['AcmeTest\Module2']);
        $this->assertEquals(array('AcmeTest\Module1'=>'testpath1','AcmeTest\Module2'=>'testpath2'), $config['module_setting']['share_setting']['paths']);
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage A class is not found:AcmeTest\None\Module
     */
    public function testConfigNotfound()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\None\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $moduleManager->getConfig();
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage Modules are not defined in module manager configuration.
     */
    public function testConfigNone()
    {
        $config = array(
            'module_manager' => array(
                'modules' => null,
            ),
        );
        $moduleManager = new ModuleManager($config);
        $moduleManager->getConfig();
    }

    /**
     * @expectedException        Aneris\Container\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument must be set array. type is invalid:string
     */
    public function testConfigInvalidType()
    {
        $config = array(
            'module_manager' => array(
                'modules' => 'abc',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $moduleManager->getConfig();
    }

    /**
     * @expectedException        Aneris\Container\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument must be set array. type is invalid:stdClass
     */
    public function testConfigInvalidObject()
    {
        $config = array(
            'module_manager' => array(
                'modules' => new stdClass(),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $moduleManager->getConfig();
    }

    public function testInitNormal()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $serviceManager = $moduleManager->getServiceLocator();
        $this->assertEquals('Aneris\Container\Container', get_class($serviceManager->get('ServiceLocator')));
        $config = $serviceManager->get('config');
        $this->assertEquals('module1', $config['module_setting']['each_setting']['AcmeTest\Module1']);
    }
/*
    public function testInitDi()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $serviceManager = $moduleManager->getServiceLocator();
        $this->assertTrue($serviceManager->has('Di'));
        $this->assertTrue($serviceManager->has('DependencyInjection'));
    }
*/
    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage The Module is not defined:None
     */
    public function testRunNotfoundModule()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $moduleManager->run('None');
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage The Module do not have invokable method for invokables configuration:AcmeTest\Module1
     */
    public function testRunNotfoundRunMethod()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $moduleManager->run('AcmeTest\Module1\Module');
    }

    public function testRunNormalAllDefault()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
            ),
            'global_config' => array(
                'execute'    => 'moduleTestRunNormal',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $this->assertEquals('AcmeTest\Module2\Module', $moduleManager->run('AcmeTest\Module2\Module'));
    }

    public function testRunNormalExplicitSelfClassName()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
                'invokables' => array(
                    'AcmeTest\Module2\Module' => 'self',
                ),
            ),
            'global_config' => array(
                'execute'    => 'moduleTestRunNormal',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $this->assertEquals('AcmeTest\Module2\Module', $moduleManager->run('AcmeTest\Module2\Module'));
    }

    public function testRunNormalExplicitClassNameInServiceManager()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
                'invokables' => array(
                    'AcmeTest\Module2\Module' => 'AcmeTest\Module2\AutorunTestInjection',
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $this->assertEquals('AcmeTest\Module2\AutorunTestInjection', $moduleManager->run('AcmeTest\Module2\Module'));
    }

    public function testRunNormalExplicitClassNameInServiceManager2()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
                'invokables' => array(
                    'AcmeTest\Module2\Module' => array(
                        'class' => 'AcmeTest\Module2\AutorunTestInjection',
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $this->assertEquals('AcmeTest\Module2\AutorunTestInjection', $moduleManager->run('AcmeTest\Module2\Module'));
    }

    public function testRunNormalExplicitClassNameInDi()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
                'invokables' => array(
                    'AcmeTest\Module2\Module' => array(
                        'class' => 'AcmeTest\Module2\AutorunTest',
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $this->assertEquals('AcmeTest\Module2\AutorunTestInjection', $moduleManager->run('AcmeTest\Module2\Module'));
    }

    /**
     * @expectedException        Aneris\Container\Exception\DomainException
     * @expectedExceptionMessage The Module do not have invokable method for invokables configuration:AcmeTest\Module2
     */
    public function testRunNormalExplicitClassNameInDiOtherMethod()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
                'invokables' => array(
                    'AcmeTest\Module2\Module' => array(
                        'class' => 'AcmeTest\Module2\AutorunTest',
                        'method' => 'none',
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $this->assertEquals('AcmeTest\Module2\AutorunTestInjection', $moduleManager->run('AcmeTest\Module2\Module'));
    }

    public function testRunGetServiceLocator()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
            ),
            'global_config' => array(
                'execute'    => 'moduleTestRunGetServiceLocator',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $serviceManager = $moduleManager->getServiceLocator();
        $result = $moduleManager->run('AcmeTest\Module2\Module');

        $smid = spl_object_hash($serviceManager);
        $resid = spl_object_hash($result); 

        $this->assertEquals($smid,$resid);
    }
/*
    public function testRunGetDi()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
            ),
            'global_config' => array(
                'execute'    => 'moduleTestRunGetDi',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $di = $moduleManager->run('AcmeTest\Module2\Module');
        $this->assertTrue(is_object($di));
        $this->assertEquals('Aneris\Container\Di',get_class($di));

        $serviceManager = $moduleManager->getServiceLocator();
        $im = $di->getServiceManager();
        $smid = spl_object_hash($serviceManager);
        $imid = spl_object_hash($im); 

        $this->assertEquals($smid,$imid);
    }
*/
    public function testRunConfigInjection()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'AcmeTest\Module1\Module' => true,
                    'AcmeTest\Module2\Module' => true,
                ),
                'invokables' => array(
                    'AcmeTest\Module2\Module' => array(
                        'class' => 'AcmeTest\Module2\AutorunTestInjection',
                        'config_injector' => 'setConfig',
                    ),
                ),
            ),
            'service_manager' => array(
                'factories' => array(
                    'AcmeTest\Module2\AutorunTestInjection' => 'self',
                ),
            ),
            'testRunConfigInjection' => array(
                'response' => 'RunRunSetConfig',
            ),
        );
        $moduleManager = new ModuleManager($config);
        $this->assertEquals('RunRunSetConfig', $moduleManager->run('AcmeTest\Module2\Module'));
    }
}
