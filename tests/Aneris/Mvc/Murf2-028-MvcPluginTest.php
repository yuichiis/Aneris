<?php
namespace MvcPluginTest;

use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Container\Container;
use Aneris\Http\Request;
use Aneris\Http\Response;
use Aneris\Mvc\Router;

// Test Target Classes
use Aneris\Mvc\PluginManager;
use Aneris\Mvc\Context;
//use Aneris\Mvc\Plugin\Redirect; // on Plugin
//use Aneris\Mvc\Plugin\Url; // on Plugin
//use Aneris\Mvc\Plugin\Placeholder; // on Plugin

class Foo
{
    public static function factory($pluginManager)
    {
        return new self($pluginManager);
    }
    public function __invoke($value)
    {
        return $value.'!!';
    }
}

class FooObject
{
    protected $pluginManager;
    public static function factory($pluginManager)
    {
        return new self($pluginManager);
    }
    public function __construct($pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }
    public function newInstance()
    {
        return $this;
    }
    public function boo($value)
    {
        return $value.'!!';
    }
    public function getContext()
    {
        return $this->pluginManager->get('Context');
    }
}
class MvcPluginTest extends \PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $config = array(
            'bar' => 'MvcPluginTest\Foo',
        );
        $sm = new Container();
        $pm = new PluginManager($sm);
        $pm->setConfig($config);
        $ctx = new Context(null,null,null,null,$pm);
        $this->assertEquals('Hello!!',$ctx->bar('Hello'));
    }
    public function testObject()
    {
        $config = array(
            'bar' => 'MvcPluginTest\FooObject',
        );
        $sm = new Container();
        $pm = new PluginManager($sm);
        $pm->setConfig($config);
        $ctx = new Context(null,null,null,null,$pm);
        $this->assertEquals('Hello!!',$ctx->bar()->boo('Hello'));
    }
    public function testServiceManagerAndContext()
    {
        $config = array(
            'bar' => 'MvcPluginTest\FooObject',
        );
        $sm = new Container();
        $pm = new PluginManager($sm);
        $ctx = new Context(null,null,null,null,$pm);
        $pm->setConfig($config,$ctx);
        $this->assertEquals(spl_object_hash($ctx),spl_object_hash($ctx->bar()->getContext()));
    }
    /**
     * @expectedException        Aneris\Mvc\Exception\DomainException
     * @expectedExceptionMessage plugin is not found.: "bar"
     */
    public function testNotfound()
    {
        $config = array(
        );
        $sm = new Container();
        $pm = new PluginManager($sm);
        $pm->setConfig($config);
        $ctx = new Context(null,null,null,null,$pm);
        $ctx->bar()->boo('Hello');
    }
    public function testRedirect()
    {
        CacheFactory::clearCache();
        $config = array(
            'plugins' => array(
                'redirect' => 'Aneris\Mvc\Plugin\Redirect',
                'url'      => 'Aneris\Mvc\Plugin\Url',
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
                    'foo2\test' => array(
                        'path' => '/test2',
                        'type' => 'segment',
                        'options' => array(
                            'parameters' => array('action','id'),
                        ),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/app/web.php';
        $_SERVER['REQUEST_URI'] = '/app/web.php/abc';
        $sm = new Container();
        $pm = new PluginManager($sm);
        $context = new Context(
            new Request(),
            new Response(),
            array('namespace'=>'foo'),
            $sm,
            $pm
        );
        $router = new Router($sm);
        $router->setConfig($config['router']);
        $pm->setConfig($config['plugins'],$context);
        $context->setRouter($router);
        $redirect = $context->redirect();
        $this->assertEquals('Aneris\Mvc\Plugin\Redirect',get_class($redirect));
        $response = $redirect->toRoute('test',array('action'=>'bar','id'=>'boo'));
        $this->assertEquals('Aneris\Http\Response',get_class($response));
        $this->assertEquals(array('Location'=>'/app/web.php/test/bar/boo'), $response->getHeaders());
        $response->resetHeaders();
        $response = $redirect->toPath('/hoge');
        $this->assertEquals(array('Location'=>'/app/web.php/hoge'), $response->getHeaders());
        $response->resetHeaders();
        $response = $redirect->toUrl('/hoge');
        $this->assertEquals(array('Location'=>'/hoge'), $response->getHeaders());
        $response->resetHeaders();
        $response = $redirect->toRoute('test',array('action'=>'bar','id'=>'boo'),array('namespace'=>'foo2'));
        $this->assertEquals('Aneris\Http\Response',get_class($response));
        $this->assertEquals(array('Location'=>'/app/web.php/test2/bar/boo'), $response->getHeaders());
        $response->resetHeaders();
    }
    public function testUrl()
    {
        CacheFactory::clearCache();
        $config = array(
            'plugins' => array(
                'url' => 'Aneris\Mvc\Plugin\Url',
            ),
            'router' => array(
                'routes' => array(
                    'foo\home' => array(
                        'path' => '/',
                        'type' => 'literal',
                    ),
                     'foo\test' => array(
                        'path' => '/test',
                        'type' => 'segment',
                        'options' => array(
                            'parameters' => array('action','id'),
                        ),
                    ),
                    'foo2\test' => array(
                        'path' => '/test2',
                        'type' => 'segment',
                        'options' => array(
                            'parameters' => array('action','id'),
                        ),
                    ),
                ),
            ),
        );
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/app/web.php';
        $_SERVER['REQUEST_URI'] = '/app/web.php/abc';
        $sm = new Container();
        $pm = new PluginManager($sm);
        $context = new Context(
            new Request(),
            new Response(),
            array('namespace'=>'foo'),
            $sm,
            $pm
        );
        $pm->setConfig($config['plugins'],$context);
        $router = new Router($sm);
        $router->setConfig($config['router']);
        $context->setRouter($router);
        $url = $context->url();
        $this->assertEquals('Aneris\Mvc\Plugin\Url',get_class($url));
        $result = $url->fromRoute('test',array('action'=>'bar','id'=>'boo'));
        $this->assertEquals('/app/web.php/test/bar/boo', $result);
        $result = $url->fromPath('/hoge');
        $this->assertEquals('/app/web.php/hoge', $result);
        $result = $url->rootPath();
        $this->assertEquals('/app/web.php', $result);
        $result = $url->prefix();
        $this->assertEquals('/app', $result);
        $result = $url->fromRoute('test',array('action'=>'bar','id'=>'boo'),array('namespace'=>'foo2'));
        $this->assertEquals('/app/web.php/test2/bar/boo', $result);
        $result = $url->fromRoute('test',array('action'=>'bar','id'=>'boo'),array('query'=>array('a'=>'b')));
        $this->assertEquals('/app/web.php/test/bar/boo?a=b', $result);
        $result = $url->fromPath('/hoge',array('query'=>array('a'=>'b')));
        $this->assertEquals('/app/web.php/hoge?a=b', $result);
        $result = $url->fromRoute('home');
        $this->assertEquals('/app/web.php', $result);
        $result = $url->fromPath('/');
        $this->assertEquals('/app/web.php', $result);
    }
    public function testPlaceholder()
    {
        $config = array(
            'plugins' => array(
                'placeholder' => 'Aneris\Mvc\Plugin\Placeholder',
            ),
        );
        $sm = new Container();
        $pm = new PluginManager($sm);
        $context = new Context(
            new Request(),
            new Response(),
            array('namespace'=>'foo'),
            $sm,
            $pm
        );
        $pm->setConfig($config['plugins'],$context);
        $router = new Router($sm);
        $this->assertEquals('DEFAULT',$context->placeholder()->get('title','DEFAULT'));
        $context->placeholder()->set('title','TEST');
        $this->assertEquals('TEST',$context->placeholder()->get('title','DEFAULT'));
    }
}
