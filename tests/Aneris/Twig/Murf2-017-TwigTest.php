<?php
namespace AnerisTest\TwigTest;

use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Container\ModuleManager;
use Aneris\Container\Container;
use Aneris\Mvc\PluginManager;
use Aneris\Mvc\Context;
use Aneris\Mvc\Router;
use Aneris\Http\Request;
use Aneris\Http\Response;

// Test Target Classes
use Twig_Environment;
use Twig_Loader_String;
use Twig_Loader_Filesystem;
// use Aneris\Module\Twig\TwigView // on ModuleManager ;
use Aneris\Module\Twig\Extension\Url;

class TwigTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/twig');
    }

    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {
        CacheFactory::clearCache();
    }

    public function testInit()
    {
        $loader = new Twig_Loader_String();
        $twig = new Twig_Environment($loader);

        $out = $twig->render('Hello {{ name }}!', array('name' => 'Tom'));
        $this->assertEquals('Hello Tom!', $out);
    }

    public function testInit2()
    {
        $loader = new Twig_Loader_Filesystem(ANERIS_TEST_RESOURCES .'/twig/templates');
        $twig = new Twig_Environment($loader, array(
            'cache' => CacheFactory::$fileCachePath .'/cache/twig',
        ));
        $out = $twig->render('index/index.twig.html', array('name' => 'Tom'));
        $this->assertEquals("Hello Tom!\n", $out);
    }

    public function testNamespace()
    {
        $loader = new Twig_Loader_Filesystem();
        $loader->addPath(ANERIS_TEST_RESOURCES.'/twig/templates/namespace1','n1');
        $loader->addPath(ANERIS_TEST_RESOURCES.'/twig/templates/namespace2','n2');
        $loader->addPath(ANERIS_TEST_RESOURCES.'/twig/templates/shared');
        $twig = new Twig_Environment($loader, array(
            'cache' => CacheFactory::$fileCachePath .'/cache/twig',
        ));
        $out = $twig->render('@n1/index/index.twig.html');
        $this->assertEquals("[layout:Namespace1]Namespace1\n", $out);
        $out = $twig->render('@n2/index/index.twig.html');
        $this->assertEquals("[layout:Namespace2]Namespace2\n", $out);
        //$out = $twig->render('@n2/index/second.html');
        //echo $out;
    }

    public function testModuleView()
    {
        $config = array(
            'twig' => array(
                'cache'    => CacheFactory::$fileCachePath.'/cache/twig',
            ),
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Module\Twig\Module' => true,
                ),
            ),
            'mvc' => array(
                'plugins' => array(
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $config = $sm->get('config');
        $viewManager = $sm->get('Aneris\Module\Twig\TwigView');
        $pm = new PluginManager($sm);
        $context = new Context(
            new Request(),
            new Response(),
            null,
            $sm,
            $pm
            );
        $pm->setConfig($config['mvc']['plugins'],$context);

        $viewManager->render(array('name'=>'Taro'),'index/index',ANERIS_TEST_RESOURCES.'/twig/templates',$context);
    }

    public function testModule()
    {
        $config = array(
            'twig' => array(
                'cache'    => CacheFactory::$fileCachePath.'/cache/twig',
                'template_paths' => ANERIS_TEST_RESOURCES.'/twig/templates',
            ),
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Module\Twig\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $twig = $moduleManager->getServiceLocator()->get('Twig_Environment');

        $out = $twig->render('index/index.twig.html', array('name' => 'Tom'));
        $this->assertEquals("Hello Tom!\n", $out);
    }

    public function testUrl()
    {
        $namespace = 'ABC';
        $config = array(
            'router' => array(
                'routes' => array(
                    $namespace.'\home' => array(
                        'path' => '/test',
                        'defaults' => array(
                            'namespace' => $namespace,
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
            'plugins' => array(
                'url' => 'Aneris\Mvc\Plugin\Url',
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/app/web.php';
        $_SERVER['REQUEST_URI'] = '/app/web.php/boo';
        $params['namespace'] = $namespace;
        $sm = new Container();
        $pm = new PluginManager($sm);
        $context = new Context(
            new Request(),
            new Response(),
            $params,
            $sm,
            $pm
            );
        $router = new Router();
        $router->setConfig($config['router']);
        $pm->setConfig($config['plugins'],$context);
        $context->setRouter($router);
        $loader = new Twig_Loader_String();
        $twig = new Twig_Environment($loader);
        $twig->addExtension(new Url($pm));

        $out = $twig->render('{{ url( "home" ) }}', array());
        $this->assertEquals('/app/web.php/test', $out);

        $out = $twig->render('{{ url( "home", {"action":"act","id":"id1"} ) }}', array());
        $this->assertEquals('/app/web.php/test/act/id1', $out);

        $out = $twig->render('{{ url( "home", {"action":"act","id":"id1"}, {"query":{"a":"b"}} ) }}', array());
        $this->assertEquals('/app/web.php/test/act/id1?a=b', $out);

        $out = $twig->render('{{ url_frompath( "/abc", {"query":{"a":"b"}} ) }}', array());
        $this->assertEquals('/app/web.php/abc?a=b', $out);

        $out = $twig->render('{{ url_root() }}', array());
        $this->assertEquals('/app/web.php', $out);

        $out = $twig->render('{{ url_prefix() }}', array());
        $this->assertEquals('/app', $out);
    }

}
