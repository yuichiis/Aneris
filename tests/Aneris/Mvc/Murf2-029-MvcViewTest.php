<?php
namespace AnerisTest\MvcViewTest;

use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Container\Container;
use Aneris\Mvc\Context;
use Aneris\Mvc\PluginManager;
use Aneris\Mvc\Router;
use Aneris\Http\Request;
use Aneris\Http\Response;

// Test Target Classes
use Aneris\Mvc\ViewManager;
use Aneris\Mvc\View;

class MvcViewTest extends \PHPUnit_Framework_TestCase
{
    public function testResolveTemplate()
    {
        $response = array('test'=>'abc');
        $templateName = 'index/index';
        $templatePaths = array(
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/local',
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/global',
        );
        $config = array();
        $sm = new Container();
        $sm->setInstance('config',$config);
        $context = new Context(
            null,
            null,
            null,
            $sm,
            null);
        $viewManager = new ViewManager();
        $result = $viewManager->resolveTemplate($templateName,$templatePaths,$context);
        $this->assertEquals(ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/local/index/index.php',$result);
        $templateName = 'layout/layout';
        $result = $viewManager->resolveTemplate($templateName,$templatePaths,$context);
        $this->assertEquals(ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/global/layout/layout.php',$result);
    }
    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage template not found: "index/none"
     */
    public function testResolveTemplateNotFound()
    {
        $response = array('test'=>'abc');
        $templateName = 'index/none';
        $templatePaths = array(
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/local',
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/global',
        );
        $config = array();
        $sm = new Container();
        $sm->setInstance('config',$config);
        $context = new Context(
            null,
            null,
            null,
            $sm,
            null);
        $viewManager = new ViewManager();
        $result = $viewManager->resolveTemplate($templateName,$templatePaths,$context);
    }
    public function testRenderTemplate()
    {
        $templateFullPath = ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/local/index/index.php';
        $templateVariables = array('test'=>'abc');
        $view = new View(new PluginManager(new Container()));
        $result = $view->renderTemplate($templateFullPath,$templateVariables);
        $this->assertEquals('abc',$result);
    }
    public function testRenderContent()
    {
        $response = array('test'=>'abc');
        $templateName = 'index/index';
        $templatePaths = array(
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/local',
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/global',
        );
        $config = array(
        );
        $response = array(
            'test' => 'abc',
        );
        $sm = new Container();
        $sm->setInstance('config',$config);
        $pm = new PluginManager($sm);
        $context = new Context(
            null,
            null,
            null,
            $sm,
            $pm);
        $pm->setConfig(array(),$context);
        $viewManager = new ViewManager();
        $answer = <<<EOD
abc
EOD;
        $result = $viewManager->render($response,$templateName,$templatePaths,$context);
        $answer = str_replace(array("\r","\n"), array("",""), $answer);
        $result = str_replace(array("\r","\n"), array("",""), $result);
        $this->assertEquals($answer,$result);
    }

    public function testRenderLayout()
    {
        $response = array('test'=>'abc');
        $templateName = 'index/index';
        $templatePaths = array(
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/local',
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/global',
        );
        $config = array(
            'mvc' => array(
                'view' => array(
                    'layout' => 'layout/layout',
                ),
            ),
        );
        $response = array(
            'test' => 'abc',
        );
        $sm = new Container();
        $sm->setInstance('config',$config);
        $pm = new PluginManager($sm);
        $context = new Context(
            null,
            null,
            null,
            $sm,
            $pm);
        $pm->setConfig(array(),$context);
        $viewManager = new ViewManager();
        $answer = <<<EOD
<html>
<head>
<title>Test</title>
</head>
<body>
abc
</body>
</html>
EOD;
        $result = $viewManager->render($response,$templateName,$templatePaths,$context);
        $answer = str_replace(array("\r","\n"), array("",""), $answer);
        $result = str_replace(array("\r","\n"), array("",""), $result);
        $this->assertEquals($answer,$result);
    }

    public function testChangeLayout()
    {
        $response = array('test'=>'abc');
        $templateName = 'index/changelayout';
        $templatePaths = array(
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/local',
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/global',
        );
        $config = array(
            'mvc' => array(
                'view' => array(
                    'layout' => 'layout/layout',
                ),
            ),
        );
        $response = array(
            'test' => 'abc',
        );
        $sm = new Container();
        $sm->setInstance('config',$config);
        $pm = new PluginManager($sm);
        $context = new Context(
            null,
            null,
            null,
            $sm,
            $pm);
        $pm->setConfig(array(),$context);
        $viewManager = new ViewManager();
        $answer = <<<EOD
Other Layout
abc
EOD;
        $result = $viewManager->render($response,$templateName,$templatePaths,$context);
        $answer = str_replace(array("\r","\n"), array("",""), $answer);
        $result = str_replace(array("\r","\n"), array("",""), $result);
        $this->assertEquals($answer,$result);
    }

    public function testChangePostfix()
    {
        $response = array('test'=>'abc');
        $templateName = 'index/postfix';
        $templatePaths = array(
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/local',
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/global',
        );
        $config = array(
            'mvc' => array(
                'view' => array(
                    'layout' => 'layout/postfix',
                    'postfix' => '.phtml',
                ),
            ),
        );
        $response = array(
            'test' => 'abc',
        );
        $sm = new Container();
        $sm->setInstance('config',$config);
        $pm = new PluginManager($sm);
        $context = new Context(
            null,
            null,
            null,
            $sm,
            $pm);
        $pm->setConfig(array(),$context);
        $viewManager = new ViewManager();
        $answer = <<<EOD
phtml Layout
test change postfix
abc
EOD;
        $result = $viewManager->render($response,$templateName,$templatePaths,$context);
        $answer = str_replace(array("\r","\n"), array("",""), $answer);
        $result = str_replace(array("\r","\n"), array("",""), $result);
        $this->assertEquals($answer,$result);
    }

    public function testPlugin()
    {
        CacheFactory::clearCache();
        $templateName = 'index/url';
        $templatePaths = array(
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/local',
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/global',
        );
        $config = array(
            'mvc' => array(
                'plugins' => array(
                    'url' => 'Aneris\Mvc\Plugin\Url',
                ),
                'router' => array(
                    'routes' => array(
                         'foo\test' => array(
                            'path' => '/test',
                            'type' => 'segment',
                            'options' => array(
                                'parameters' => array('action','id'),
                            ),
                        ),
                    ),
                ),
            ),
        );
        $response = array(
            'test' => 'abc',
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/app/web.php';
        $_SERVER['REQUEST_URI'] = '/app/web.php/abc';
        $sm = new Container();
        $sm->setInstance('config',$config);
        $pm = new PluginManager($sm);
        $context = new Context(
            new Request(),
            new Response(),
            array('namespace'=>'foo'),
            $sm,
            $pm);
        $pm->setConfig($config['mvc']['plugins'],$context);
        $router = new Router($sm);
        $router->setConfig($config['mvc']['router']);
        $context->setRouter($router);
        $viewManager = new ViewManager();
        $answer = <<<EOD
/app/web.php/test/bar/boo
EOD;
        $result = $viewManager->render($response,$templateName,$templatePaths,$context);
        $answer = str_replace(array("\r","\n"), array("",""), $answer);
        $result = str_replace(array("\r","\n"), array("",""), $result);
        $this->assertEquals($answer,$result);
    }

    public function testNoContent()
    {
        $response = null;
        $templateName = 'index/novariable';
        $templatePaths = array(
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/local',
            ANERIS_TEST_RESOURCES.'/AcmeTest/MvcView/Resources/views/global',
        );
        $config = array(
        );
        $sm = new Container();
        $sm->setInstance('config',$config);
        $pm = new PluginManager($sm);
        $context = new Context(
            null,
            null,
            null,
            $sm,
            $pm);
        $pm->setConfig(array(),$context);
        $viewManager = new ViewManager();
        $answer = <<<EOD
Hello
EOD;
        $result = $viewManager->render($response,$templateName,$templatePaths,$context);
        $answer = str_replace(array("\r","\n"), array("",""), $answer);
        $result = str_replace(array("\r","\n"), array("",""), $result);
        $this->assertEquals($answer,$result);
    }
}
