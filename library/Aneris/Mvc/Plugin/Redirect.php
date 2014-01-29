<?php
namespace Aneris\Mvc\Plugin;

use Aneris\Mvc\Exception;

class Redirect
{
    protected $context;

    public static function factory($pluginManager)
    {
        return new self($pluginManager);
    }
    
    public function __construct($pluginManager)
    {
        $this->context = $pluginManager->get('Context');
        return $this;
    }

    public function toRoute($routeName,array $params=array(),$options=array())
    {
        return $this->context->getHttpResponse()->addHeader(
            'Location',$this->context->url()->fromRoute($routeName,$params,$options));
    }

    public function toPath($path,$options=array())
    {
        return $this->context->getHttpResponse()->addHeader(
            'Location',$this->context->url()->fromPath($path,$options));
    }

    public function toUrl($url)
    {
        return $this->context->getHttpResponse()->addHeader('Location',$url);
    }
}
