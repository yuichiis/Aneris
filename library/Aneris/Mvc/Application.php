<?php
namespace Aneris\Mvc;

use Aneris\Http\Request as HttpRequest;
use Aneris\Http\Response as httpResponse;
use Aneris\Container\ServiceLocatorInterface;

class Application
{
    protected $config;
    protected $serviceLocator;
    protected $router;
    protected $dispatcher;
    protected $renderer;
    protected $testMode;

    public function __construct(
        ServiceLocatorInterface $serviceLocator=null,
        Router $router=null,
        Dispatcher $dispatcher=null,
        Renderer $renderer=null)
    {
        $this->serviceLocator = $serviceLocator;
        if($router)
            $this->router = $router;
        else
            $this->router = new Router($this->serviceLocator);

        if($dispatcher)
            $this->dispatcher = $dispatcher;
        else
            $this->dispatcher = new Dispatcher($this->serviceLocator);

        if($renderer)
            $this->renderer = $renderer;
        else
            $this->renderer = new Renderer($this->serviceLocator);
    }

    public function setConfig($config=null)
    {
        $this->config = $config;//['mvc'];
        if(isset($this->config['router']))
            $this->getRouter()->setConfig($this->config['router']);
        if(isset($this->config['dispatcher']))
            $this->getDispatcher()->setConfig($this->config['dispatcher']);
        if(isset($this->config['view']))
            $this->getRenderer()->setConfig($this->config['view']);
        if(isset($this->config['plugin']))
            $this->getPluginManager()->setConfig($this->config['plugin']);
    }

    public function setServiceLocator($sm=null)
    {
        $this->serviceLocator = $sm;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    public function getRenderer()
    {
        return $this->renderer;
    }

    public function setTestMode($mode=true)
    {
        $this->setMode = $mode;
    }

    public function run()
    {
        $param = array();
        $pluginManager = new PluginManager($this->serviceLocator);
        $context = new Context(
            new HttpRequest(),
            new HttpResponse(),
            null,
            $this->serviceLocator,
            $pluginManager
        );
        try {
            if(isset($this->config['plugins']))
                $config = $this->config['plugins'];
            else
                $config = null;
            $pluginManager->setConfig($config,$context);

            // routing from request
            $router = $this->getRouter();
            $context->setRouter($router);
            $router->match($context);

            // dispatch to controller
            $dispatcher = $this->getDispatcher();
            $response = $dispatcher->dispatch($context);

            // render view
            $renderer = $this->getRenderer();
            $httpResponse = $renderer->render($response,$context);
            $httpResponse->send();
        }
        catch(\PHPUnit_Framework_Error_Notice $e) {
            throw $e;
        }
        catch(\Exception $e) {
            if($this->testMode)
                throw $e;
            $this->exceptionAction($e,$context);
            return;
        }
    }

    protected function exceptionAction($e,$context)
    {
        $renderer = $this->getRenderer();
        $httpResponse = $renderer->renderError($e,$context);
        $httpResponse->send();
    }
}
