<?php
namespace AnerisTest\MvcRouterTest;

use Aneris\Http\Request;
use Aneris\Http\Response;
use Aneris\Mvc\Context;

// Test Target Classes
use Aneris\Mvc\Router;

class MvcRouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \Aneris\Stdlib\Cache\CacheFactory::clearCache();
    }

    public function testMatchPathRoot()
    {
        $config = array(
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
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/';
        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();

        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/', $route['path']);

        $param = $router->parseParameter($path, $route);
        $this->assertEquals(0, count($param));

        $param = $router->setDefaultParameter($param, $route);
        $this->assertEquals(2, count($param));
        $this->assertEquals('Index', $param['controller']);
        $this->assertEquals('index', $param['action']);
    }

    public function testMatchPath()
    {
        $config = array(
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
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo';
        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();

        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);

        $param = $router->parseParameter($path, $route);
        $this->assertEquals(0, count($param));

        $param = $router->setDefaultParameter($param, $route);
        $this->assertEquals(2, count($param));
        $this->assertEquals('Boo', $param['controller']);
        $this->assertEquals('index', $param['action']);
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\PageNotFoundException
     * @expectedExceptionMessage A route is not found.
     */
    public function testUnMatchPath()
    {
        $config = array(
            'routes' => array(
                'home' => array(
                    'path' => '/root',
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
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/boo';
        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();

        $route = $router->matchRoute($path,$request);
        $this->assertFalse($route);
    }

    public function testMatchParam()
    {
        $config = array(
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
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();

        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);

        $param = $router->parseParameter($path, $route);
        $this->assertEquals(1, count($param));
        $this->assertEquals('bar', $param['action']);

        $param = $router->setDefaultParameter($param, $route);
        $this->assertEquals(2, count($param));
        $this->assertEquals('Boo', $param['controller']);
        $this->assertEquals('bar', $param['action']);
    }

    public function testMatchParamNonLeft()
    {
        $config = array(
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
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foobar';
        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();

        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);

        $param = $router->parseParameter($path, $route);
        $this->assertEquals(1, count($param));
        $this->assertEquals('bar', $param['action']);

        $param = $router->setDefaultParameter($param, $route);
        $this->assertEquals(2, count($param));
        $this->assertEquals('Boo', $param['controller']);
        $this->assertEquals('bar', $param['action']);
    }

    public function testMatchParamDashRight()
    {
        $config = array(
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
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar/';
        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();

        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);

        $param = $router->parseParameter($path, $route);
        $this->assertEquals(1, count($param));
        $this->assertEquals('bar', $param['action']);

        $param = $router->setDefaultParameter($param, $route);
        $this->assertEquals(2, count($param));
        $this->assertEquals('Boo', $param['controller']);
        $this->assertEquals('bar', $param['action']);
    }

    public function testMatchParam2()
    {
        $config = array(
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
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar/abc';
        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();

        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);

        $param = $router->parseParameter($path, $route);
        $this->assertEquals(2, count($param));
        $this->assertEquals('bar', $param['action']);
        $this->assertEquals('abc', $param['id']);

        $param = $router->setDefaultParameter($param, $route);
        $this->assertEquals(3, count($param));
        $this->assertEquals('Boo', $param['controller']);
        $this->assertEquals('bar', $param['action']);
        $this->assertEquals('abc', $param['id']);
    }

    public function testMatchParamOver()
    {
        $config = array(
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
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar/abc/xyz';
        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();

        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);

        $param = $router->parseParameter($path, $route);
        $this->assertEquals(2, count($param));
        $this->assertEquals('bar', $param['action']);
        $this->assertEquals('abc', $param['id']);

        $param = $router->setDefaultParameter($param, $route);
        $this->assertEquals(3, count($param));
        $this->assertEquals('Boo', $param['controller']);
        $this->assertEquals('bar', $param['action']);
        $this->assertEquals('abc', $param['id']);
    }

    public function testMatchMethod()
    {
        $config = array(
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
                'foo\Post' => array(
                    'path' => '/foo',
                    'defaults' => array(
                        'controller' => 'Boo',
                        'action' => 'post',
                    ),
                    'conditions' => array(
                        'method' => 'POST',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('id'),
                    ),
                ),
                'foo\Get' => array(
                    'path' => '/foo',
                    'defaults' => array(
                        'controller' => 'Boo',
                        'action' => 'get',
                    ),
                    'conditions' => array(
                        'method' => 'GET',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/abc/def';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();
        
        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);
        $this->assertEquals('get', $route['defaults']['action']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);
        $this->assertEquals('post', $route['defaults']['action']);
    }

    public function testMatchHeaders()
    {
        $config = array(
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
                'foo\html' => array(
                    'path' => '/foo',
                    'defaults' => array(
                        'controller' => 'Boo',
                        'action' => 'html',
                    ),
                    'conditions' => array(
                        'headers' => array(
                            'Accept' => 'text/html',
                            'Accept-Language' => 'ja',
                        ),
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('id'),
                    ),
                ),
                'foo\json' => array(
                    'path' => '/foo',
                    'defaults' => array(
                        'controller' => 'Boo',
                        'action' => 'json',
                    ),
                    'conditions' => array(
                        'headers' => array(
                            'Accept' => 'application/json',
                        ),
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/abc/def';
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja,en-US;q=0.8,en;q=0.6';

        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();
        
        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);
        $this->assertEquals('html', $route['defaults']['action']);

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US;q=0.8,en;q=0.6';
        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/', $route['path']);

        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja,en-US;q=0.8,en;q=0.6';

        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);
        $this->assertEquals('json', $route['defaults']['action']);
    }

    public function testMatch()
    {
        $config = array(
            'routes' => array(
                'home' => array(
                    'path' => '/',
                    'defaults' => array(
                        'parameters' => array('action', 'id'),
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
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo/bar/abc/xyz';

        $router = new Router();
        $router->setConfig($config);
        $context = new Context(
            new Request()
        );

        $param = $router->match($context)->getParams();
        $this->assertEquals(3, count($param));
        $this->assertEquals('Boo', $param['controller']);
        $this->assertEquals('bar', $param['action']);
        $this->assertEquals('abc', $param['id']);
    }

    public function testMatch2()
    {
        $config = array(
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
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/bar/abc/xyz';

        $router = new Router();
        $router->setConfig($config);
        $context = new Context(
            new Request()
        );

        $param = $router->match($context)->getParams();
        $this->assertEquals(3, count($param));
        $this->assertEquals('Index', $param['controller']);
        $this->assertEquals('bar', $param['action']);
        $this->assertEquals('abc', $param['id']);
    }

    /**
     * @expectedException        Aneris\Mvc\Exception\PageNotFoundException
     * @expectedExceptionMessage A literal route has sub-directory on route "home".
     */
    public function testLiteralHasSubDirectory()
    {
        $config = array(
            'routes' => array(
                'home' => array(
                    'path' => '/',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                    'type' => 'literal',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
                'foo' => array(
                    'path' => '/foo',
                    'defaults' => array(
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/boo';

        $router = new Router();
        $router->setConfig($config);
        $context = new Context(
            new Request()
        );

        $param = $router->match($context)->getParams();
        $this->assertFalse($param);
    }


    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage Unkown route type "UNKNOWN" in route "home"
     */
    public function testUnkownRouteType()
    {
        $config = array(
            'routes' => array(
                'home' => array(
                    'path' => '/',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                    'type' => 'UNKNOWN',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/';

        $router = new Router();
        $router->setConfig($config);
        $context = new Context(
            new Request()
        );

        $param = $router->match($context)->getParams();
        $this->assertFalse($param);
    }

    public function testTypeOfExplicitClassName()
    {
        $config = array(
            'routes' => array(
                'home' => array(
                    'path' => '/',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                    'type' => 'Aneris\Mvc\Router\SegmentParser',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/test/123';

        $router = new Router();
        $router->setConfig($config);
        $context = new Context(
            new Request()
        );

        $param = $router->match($context)->getParams();
        $this->assertEquals('123',$param['id']);
    }

    public function testAssembleSegment()
    {
        $namespace = 'fooname';
        $config = array(
            'routes' => array(
                $namespace.'\home' => array(
                    'path' => '/',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                    'type' => 'Aneris\Mvc\Router\SegmentParser',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
                $namespace.'\foo' => array(
                    'path' => '/foo',
                    'defaults' => array(
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'segment',
                    'options' => array(
                        'parameters' => array('action', 'id'),
                    ),
                ),
                $namespace.'\bar' => array(
                    'path' => '/bar',
                    'defaults' => array(
                        'controller' => 'Boo',
                        'action' => 'index',
                    ),
                    'type' => 'literal',
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/test/123';
        $router = new Router();
        $router->setConfig($config);
        $params['namespace'] = $namespace;
        $context = new Context(
            new Request(),
            new Response(),
            $params
        );
        $context->setRouter($router);

        $path = $router->assemble($context,'home',array('action'=>'test','id'=>123));
        $this->assertEquals('/test/123',$path);

        $path = $router->assemble($context,'home');
        $this->assertEquals('/',$path);

        $path = $router->assemble($context,'foo',array('action'=>'test','id'=>123));
        $this->assertEquals('/foo/test/123',$path);

        $path = $router->assemble($context,'foo');
        $this->assertEquals('/foo',$path);

        $path = $router->assemble($context,'bar');
        $this->assertEquals('/bar',$path);
    }


    public function testYamlConfig()
    {
        if(!extension_loaded('yaml'))
            return;
        //$yaml = <<<EOD
//routes: 
//    %__INCLUDE(%__DIR__%/AcmeTest/MvcRouter/config/routing.yml)__%
//EOD;
        //$yaml = str_replace('%__DIR__%', str_replace('\\','\\\\',__DIR__),$yaml);
        //$config = yaml_parse($yaml); 
        $config = array(
            'routes' => yaml_parse_file(ANERIS_TEST_RESOURCES.'/AcmeTest/MvcRouter/config/routing.yml')
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/foo';
        $router = new Router();
        $router->setConfig($config);
        $request = new Request();
        $path = $request->getPath();

        $route = $router->matchRoute($path,$request);
        $this->assertEquals('/foo', $route['path']);

        $param = $router->parseParameter($path, $route);
        $this->assertEquals(0, count($param));

        $param = $router->setDefaultParameter($param, $route);
        $this->assertEquals(2, count($param));
        $this->assertEquals('Boo', $param['controller']);
        $this->assertEquals('index', $param['action']);
    }

}
