<?php
namespace AnerisTest\SmartyTest;

use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Container\ModuleManager;
use Aneris\Mvc\Context;
use Aneris\Http\Request;
use Aneris\Http\Response;

// Test Target Classes
use Smarty;
//use Aneris\Module\Smarty\SmartyView; // on ModuleManager

class SmartyTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/smarty/templates_c');
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/smarty/cache');
    }

    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {
    }

    public function testDummy()
    {
        $this->assertTrue(true);
    }

    public function testDefault()
    {
        if(!class_exists('Smarty'))
            return;
        $smarty = new Smarty();
        $smarty->template_dir = ANERIS_TEST_RESOURCES.'/smarty/templates/';
        $smarty->config_dir   = ANERIS_TEST_RESOURCES.'/smarty/configs/';
        $smarty->compile_dir  = CacheFactory::$fileCachePath.'/cache/smarty/templates_c/';
        $smarty->cache_dir    = CacheFactory::$fileCachePath.'/cache/smarty/cache/';

        $smarty->assign('name','Taro');
        //$smarty->debugging = true;

        $out = $smarty->fetch('index/index.tpl.html');
        $this->assertEquals("Hello Taro", $out);
    }

    public function testModuleView()
    {
        if(!class_exists('Smarty'))
            return;
        $config = array(
            'smarty' => array(
                'config_dir'  => ANERIS_TEST_RESOURCES.'/smarty/configs/',
                'cache_dir'   => CacheFactory::$fileCachePath.'/cache/smarty/',
                'compile_dir' => CacheFactory::$fileCachePath.'/cache/smarty/templates_c/',
                'caching'     => 1,
            ),
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Module\Smarty\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $viewManager = $moduleManager->getServiceLocator()->get('Aneris\Module\Smarty\SmartyView');
        $context = new Context(new Request(),new Response(),null,$sm);

        $out = $viewManager->render(array('name'=>'Taro'),'index/index',ANERIS_TEST_RESOURCES.'/smarty/templates',$context);
        $this->assertEquals("Hello Taro", $out);
    }

    public function testModule()
    {
        if(!class_exists('Smarty'))
            return;
        $config = array(
            'smarty' => array(
                'template_dir'=> ANERIS_TEST_RESOURCES.'/smarty/templates/',
                'config_dir'  => ANERIS_TEST_RESOURCES.'/smarty/configs/',
                'cache_dir'   => CacheFactory::$fileCachePath.'/cache/smarty/',
                'compile_dir' => CacheFactory::$fileCachePath.'/cache/smarty/templates_c/',
                'caching'     => 1,
            ),
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Module\Smarty\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $smarty = $moduleManager->getServiceLocator()->get('Smarty');

        $smarty->assign('name','Taro');

        $out = $smarty->fetch('index/index.tpl.html');
        $this->assertEquals("Hello Taro", $out);
    }
}
