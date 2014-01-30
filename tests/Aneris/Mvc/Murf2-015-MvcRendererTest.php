<?php
namespace AnerisTest\MvcRendererTest;

use Aneris\Container\ServiceManager;
use Aneris\Container\ServiceLocatorInterface;

use Aneris\Mvc\ViewManagerInterface;
use Aneris\Mvc\Context;
use Aneris\Http\Request;
use Aneris\Http\Response;

// Test Target Classes
use Aneris\Mvc\Renderer;

class TestViewMamager implements ViewManagerInterface
{
    public function render($response,$templateName,$templatePaths,$context)
    {
        if(!is_array($templatePaths))
            return $templatePaths.'/'.$templateName;
        return 'array('.implode(',', $templatePaths).')/'.$templateName;
    }
}

class MvcRendererTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //$this->context = new Context(new Request(),new Response());
    }

    public function testNormalNamespace()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates)/foo/bar');
    }

    public function testNamespaceLess()
    {
        //$param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates'.')'.'/foo/bar');
    }

    public function testDefaultNamespace()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'default' => ANERIS_TEST_RESOURCES.'/twig/templates/default',
                        //'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates/default)/foo/bar');
    }

    public function testArrayPaths()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'default' => array(
                            ANERIS_TEST_RESOURCES.'/twig/templates/default',
                            ANERIS_TEST_RESOURCES.'/twig/templates/default2',
                        ),
                        'Aneris2MvcDispatcherTest' => array(
                            ANERIS_TEST_RESOURCES.'/twig/templates',
                            ANERIS_TEST_RESOURCES.'/twig/templates2',
                        ),
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString(
            'array('.ANERIS_TEST_RESOURCES.'/twig/templates,'.
                     ANERIS_TEST_RESOURCES.'/twig/templates2,'.
                     ANERIS_TEST_RESOURCES.'/twig/templates/default,'.
                     ANERIS_TEST_RESOURCES.'/twig/templates/default2'.
                     ')/foo/bar');
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage Template_paths is not matching in view config.
     */
    public function testNamespaceNotfound()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates'.')'.'/foo/bar');
    }

    public function testExplicitService()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'service' => array(
                        'Aneris2MvcDispatcherTest' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                    'template_paths' => array(
                        'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates)/foo/bar');
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage a class of view manager is not found.: None
     */
    public function testServiceNotfound()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'None',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString(ANERIS_TEST_RESOURCES.'/twig/templates/foo/bar');
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage Template_paths is not found in view config.
     */
    public function testViewConfigNotfound()
    {
        //$param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $config = array(
            'mvc' => array(
            ),
        );

        $renderer = new Renderer();
        //$renderer->setConfig($config['mvc']['view']);

        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString(ANERIS_TEST_RESOURCES.'/twig/templates/foo/bar');
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage Template_paths is not found in view config.
     */
    public function testPathConfigNotfound()
    {
        //$param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString(ANERIS_TEST_RESOURCES.'/twig/templates/foo/bar');
    }

    public function testRenderError()
    {
        //$param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['controller'] = 'foo';
        $param['action'] = 'bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $e = new \Exception('error');
        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->renderError($e,$context);
        $httpResponse->send();

        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates'.')'.'/error/503');
    }

    public function testViewParameter()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['view'] = 'foo/bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(new Request(),new Response(),$param);
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates)/foo/bar');
    }

    public function testViewOnContext()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['view'] = 'foo/bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(
            new Request(),
            new Response(),
            $param);
        $context->setView('setview/oncontext');
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $httpResponse->send();
        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates)/setview/oncontext');
    }

    public function testViewOnResponse()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['view'] = 'foo/bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(
            new Request(),
            new Response(),
            $param);
        $context->setView('setview/oncontext');
        $httpResponse = $renderer->render('setview/response',$context);
        $httpResponse->send();
        $this->expectOutputString('array('.ANERIS_TEST_RESOURCES.'/twig/templates)/setview/response');
    }

    public function testAttributeOnResponse()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['view'] = 'foo/bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(
            new Request(),
            new Response(),
            $param);
        $context->setAttributes(array('x'=>'y'));
        $httpResponse = $renderer->render(array('a'=>'b'),$context);
        $this->assertEquals(array('a'=>'b'),$context->getAttributes());
    }

    public function testAttributeOnContext()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $param['view'] = 'foo/bar';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(
            new Request(),
            new Response(),
            $param);
        $context->setAttribute('x','y');
        $httpResponse = $renderer->render('setview/response',$context);
        $this->assertEquals(array('x'=>'y'),$context->getAttributes());
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage view name is not specified.
     */
    public function testViewNameNotSpecified()
    {
        $param['namespace'] = 'Aneris2MvcDispatcherTest';
        $config = array(
            'mvc' => array(
                'view' => array(
                    'template_paths' => array(
                        'Aneris2MvcDispatcherTest' => ANERIS_TEST_RESOURCES.'/twig/templates',
                    ),
                    'service' => array(
                        'default' => 'AnerisTest\MvcRendererTest\TestViewMamager',
                    ),
                ),
            ),
        );

        $renderer = new Renderer();
        $renderer->setConfig($config['mvc']['view']);

        $context = new Context(
            new Request(),
            new Response(),
            $param);
        $httpResponse = $renderer->render(array(),$context);
    }
}
