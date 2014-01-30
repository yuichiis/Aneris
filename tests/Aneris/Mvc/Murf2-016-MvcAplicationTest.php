<?php
namespace AnerisTest\MvcApplicationTest;

use Aneris\Container\ServiceManager;
use Aneris\Container\ServiceLocatorInterface;
use Aneris\Container\ModuleManager;
use Aneris\Mvc\ViewManagerInterface;
use Aneris\Stdlib\Cache\CacheFactory;

// Test Target Classes
use Aneris\Mvc\Application;

class FooController
{
    public function indexAction()
    {
        return array('name' => __METHOD__);
    }

    public function barAction()
    {
        return array('name' => __METHOD__);
    }

    public function test2()
    {
        return array('name' => __METHOD__);
    }

    public function exceptionAction()
    {
        throw new \Exception('Error!');
        return array('name' => __METHOD__);
    }

    public function phpuniterrorAction()
    {
        echo $a;
        return array('name' => __METHOD__);
    }
}

class Foo2Controller
{
    public function barAction()
    {
        return array('name' => __METHOD__);
    }
}

class Foo3
{
    public function barAction()
    {
        return array('name' => __METHOD__);
    }
}


class ViewMamager implements ViewManagerInterface
{
    public static function factory($serviceManager=null)
    {
        return new self();
    }

    public function render($response,$templateName,$templatePaths,$context)
    {
        if(is_array($templatePaths))
            $templatePathString = 'array('.implode(':', $templatePaths).')';
        else
            $templatePathString = $templatePaths;

        $html = $templatePathString.'/'.$templateName;

        if(isset($response['policy']['display_detail']) && $response['policy']['display_detail']) {
            $html .= "\n".$response['exception']."\n".
            $response['code']."\n".
            $response['message']."\n".
            $response['file']."\n".
            $response['line']."\n".
            $response['trace']."\n";
        }

        return $html;
    }
}

class ErrorViewMamager implements ViewManagerInterface
{
    public static function factory($serviceManager=null)
    {
        return new self();
    }

    public function render($response,$templateName,$templatePaths,$context)
    {
        throw new \Exception('Error in View Manager!!');
    }
}

class MvcApplicationTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public function setUp()
    {
        CacheFactory::clearCache();
    }

    public function testNormal()
    {
        $config = array(
            'mvc' => array(
                'router' => array(
                    'routes' => array(
                        'home' => array(
                            'path' => '/',
                            'defaults' => array(
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                            'type' => 'segment',
                            'options' => array(
                                'action',
                                'id',
                            ),
                        ),
                        'foo' => array(
                            'path' => '/foo',
                            'defaults' => array(
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                            'type' => 'segment',
                            'options' => array(
                                'parameters' => array('action', 'id'),
                            ),
                        ),
                    ),
                ),
                'dispatcher' => array(
                    'invokables' => array(
                        'Index' => 'AnerisTest\MvcApplicationTest\FooController',
                    ),
                ),
                'view' => array(
                    'service' => array(
                        'default' => 'AnerisTest\MvcApplicationTest\ViewMamager',
                    ),
                    'template_paths' => array(
                        ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo';

        $app = new Application();
        $app->setConfig($config['mvc']);
        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates)/index/index');
        $app->run();
    }

    public function testNamespaceNotfound()
    {
        $config = array(
            'mvc' => array(
                'router' => array(
                    'routes' => array(
                        'home' => array(
                            'path' => '/',
                            'defaults' => array(
                                'namespace' => 'Foo\Space',
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                            'type' => 'literal',
                        ),
                        'foo' => array(
                            'path' => '/foo',
                            'defaults' => array(
                                'namespace' => 'FooBar\Space',
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                            'type' => 'segment',
                            'options' => array(
                                'parameters' => array('action', 'id'),
                            ),
                        ),
                    ),
                ),
                'dispatcher' => array(
                    'invokables' => array(
                        'Foo\Space\Index' => 'AnerisTest\MvcApplicationTest\FooController',
                    ),
                ),
                'view' => array(
                    'template_paths' => array(
                        ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcApplicationTest\ViewMamager',
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/boo';

        $app = new Application();
        $app->setConfig($config['mvc']);

        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates)/error/404');
        $app->run();
    }

    public function testServerErrorByExceptionInController()
    {
        $config = array(
            'mvc' => array(
                'router' => array(
                    'routes' => array(
                        'home' => array(
                            'path' => '/',
                            'defaults' => array(
                                'namespace' => 'Foo\Space',
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                            'type' => 'segment',
                            'options' => array(
                                'parameters' => array('action', 'id'),
                            ),
                        ),
                    ),
                ),
                'dispatcher' => array(
                    'invokables' => array(
                        'Foo\Space\Index' => 'AnerisTest\MvcApplicationTest\FooController',
                    ),
                ),
                'view' => array(
                    'template_paths' => array(
                        'Foo\Space' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'error_policy' => array(
                        'display_detail' => false,
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcApplicationTest\ViewMamager',
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/exception';

        $app = new Application();
        $app->setConfig($config['mvc']);

        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates)/error/503');
        $app->run();
    }

    public function testServerErrorByExceptionInViewManager()
    {
        $config = array(
            'mvc' => array(
                'router' => array(
                    'routes' => array(
                        'home' => array(
                            'path' => '/',
                            'defaults' => array(
                                'namespace' => 'Foo\Space',
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                            'type' => 'segment',
                            'options' => array(
                                'parameters' => array('action', 'id'),
                            ),
                        ),
                    ),
                ),
                'dispatcher' => array(
                    'invokables' => array(
                        'Foo\Space\Index' => 'AnerisTest\MvcApplicationTest\FooController',
                    ),
                ),
                'view' => array(
                    'template_paths' => array(
                        'Foo\Space' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'error_policy' => array(
                        'display_detail' => true,
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcApplicationTest\ErrorViewMamager',
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/exception';

        $app = new Application();
        $app->setConfig($config['mvc']);

        $this->expectOutputRegex('/Error in View Manager/');
        $app->run();
    }

    public function testRunOnDiByModuleManager()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Mvc\Module'=>true,
                ),
            ),
            'mvc' => array(
                'router' => array(
                    'routes' => array(
                        'home' => array(
                            'path' => '/',
                            'defaults' => array(
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                            'type' => 'segment',
                            'options' => array(
                                'parameters' => array('action', 'id'),
                            ),
                        ),
                        'foo' => array(
                            'path' => '/foo',
                            'defaults' => array(
                                'controller' => 'Index',
                                'action' => 'index',
                            ),
                            'type' => 'segment',
                            'options' => array(
                                'parameters' => array('action', 'id'),
                            ),
                        ),
                    ),
                ),
                'dispatcher' => array(
                    'invokables' => array(
                        'Index' => 'AnerisTest\MvcApplicationTest\FooController',
                    ),
                ),
                'view' => array(
                    'template_paths' => array(
                        ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcApplicationTest\ViewMamager',
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo';

        $moduleManager = new ModuleManager($config);

        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates)/index/index');
        $moduleManager->run('Aneris\Mvc\Module');
        $app = $moduleManager->getServiceLocator()->get('Aneris\Mvc\Application');
        $this->assertEquals('Aneris\Container\Container',get_class($app->getServiceLocator()));
    }

}
