<?php
namespace Aneris\Mvc;

use Aneris\Http\HttpResponseInterface;
use Aneris\Container\ServiceLocatorInterface;

class Renderer
{
    const DEFAULT_MVC_VIEW_MANAGER = 'Aneris\Mvc\ViewManager';
    protected $serviceLocator;
    protected $config;

    public function __construct($serviceLocator=null)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function setServiceLocator($sm)
    {
        $this->serviceLocator = $sm;
    }

    public function render($response,Context $context)
    {
        $param=$context->getParams();
        if($response instanceof HttpResponseInterface) {
            return $response;
        } else if(is_string($response)) {
            $context->setView($response);
        } else if(is_array($response)) {
            $context->setAttributes($response);
        } else {
            throw new Exception\DomainException('invalid response type: '.gettype($response));
        }

        $templatePath = array();
        $config = $this->config;
        if(isset($param['namespace'])) {
            if(isset($config['template_paths'][$param['namespace']])) {
                $templatePath = $config['template_paths'][$param['namespace']];
                if(!is_array($templatePath))
                    $templatePath = array($templatePath);
            }
        } else {
            if(!isset($config['template_paths']))
                throw new Exception\DomainException('Template_paths is not found in view config.');
            $templatePath = array();
            foreach($config['template_paths'] as $key => $tmp) {
                if($key==='default')
                    continue;
                if(!is_array($tmp))
                    $tmp = array($tmp);
                $templatePath = array_merge($templatePath,$tmp);
            }
        }
        if(isset($config['template_paths']['default'])) {
            $defaultPath = $config['template_paths']['default'];
            if(!is_array($defaultPath))
                $defaultPath = array($defaultPath);
            $templatePath = array_merge($templatePath,$defaultPath);
        }
        if(count($templatePath)==0)
            throw new Exception\DomainException('Template_paths is not matching in view config.');

        $templateName = $context->getView();
        if($templateName===null) {
            if(isset($param['view']))
                $templateName = $param['view'];
            else if(isset($param['controller']) && $param['action'])
                $templateName = strtolower($param['controller']).'/'.strtolower($param['action']);
            else
                throw new Exception\DomainException('view name is not specified.');
        }

        if(isset($param['namespace']) && isset($config['service'][$param['namespace']])) {
            $viewManagerName = $config['service'][$param['namespace']];
        } else if(isset($config['service']['default'])){
            $viewManagerName = $config['service']['default'];
        } else {
            $viewManagerName = self::DEFAULT_MVC_VIEW_MANAGER;
        }
        if($this->serviceLocator) {
            $viewManager = $this->serviceLocator->get($viewManagerName);
        } else {
            if(!class_exists($viewManagerName))
                throw new Exception\DomainException('a class of view manager is not found.: '.$viewManagerName);
            $viewManager = new $viewManagerName();
        }

        $httpResponse = $context->getHttpResponse();
        $context->setRouteInformation('view',$templateName);
        $httpResponse->setContent($viewManager->render($context->getAttributes(),$templateName,$templatePath,$context));
        return $httpResponse;
    }

    public function renderError(\Exception $e, Context $context)
    {
        $httpResponse = $context->getHttpResponse();
        $config = $this->config;
        if(isset($config['error_policy']))
            $policy = $config['error_policy'];
        else
            $policy = null;
        if(isset($policy['redirect_url'])) {
            return $this->redirect($policy['redirect_url'],$httpResponse);
        }

        $route = $context->getParams();
        list($response,$param,$page) = $this->buildErrorInformation($e,$route,$context);
        $context->setParams($param);
        $context->setView($param['controller'].'/'.$param['action']);

        try {
            $httpResponse = $this->render($response,$context);
            $httpResponse->setStatusCode(intval($page));
        }
        catch(\PHPUnit_Framework_Error_Notice $e) {
            throw $e;
        }
        catch(\Exception $e) {
            $this->renderRawResponse($e,$route,$httpResponse,$context);
        }
        return $httpResponse;
    }

    public function redirect($url,HttpResponseInterface $httpResponse)
    {
        $httpResponse->addHeader('Location',$url);
        return $httpResponse;
    }

    public function renderRawResponse($e,$route,$httpResponse,$context)
    {
        list($response,$param,$page) = $this->buildErrorInformation($e,$route,$context);
        $html = "<pre>\n";
        foreach ($response as $key => $value) {
            if(is_scalar($value))
                $html .= $key.': '.$value."\n";
        }
        $html .= "</pre>\n";
        $httpResponse->setContent($html);
    }

    protected function buildErrorInformation($e,$route,$context)
    {
        $config = $this->config;
        if(isset($config['error_policy']))
            $policy = $config['error_policy'];
        else
            $policy = null;

        $ep = $e;
        $trace = '';
        while($ep) {
            $trace .= $ep->getTraceAsString();
            $ep = $ep->getPrevious();
        }
        $response = array(
            'route'     => $context->getRouteInformation('route'),
            'namespace' => isset($route['namespace']) ? $route['namespace'] : null,
            'controller' => isset($route['controller']) ? $route['controller'] : null,
            'action'    => isset($route['action']) ? $route['action'] : null,
            'method'    => $context->getRouteInformation('method'),
            'view'      => $context->getRouteInformation('view'),
            'policy'    => $policy,
            'exception' => get_class($e),
            'code'      => $e->getCode(),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $trace,
        );

        if($e instanceof Exception\PageNotFoundException) {
            if(isset($policy['not_found_page']))
                $page = $policy['not_found_page'];
            else
                $page = '404';
        }
        else {
            if(isset($policy['exception_page']))
                $page = $policy['exception_page'];
            else
                $page = '503';
        }
        $param = array(
            'controller' => 'error',
            'action'     => $page,
        );
        return array($response,$param,$page);
    }
}