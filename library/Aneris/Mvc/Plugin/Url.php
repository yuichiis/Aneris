<?php
namespace Aneris\Mvc\Plugin;

use Aneris\Mvc\Exception;

class Url
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

    public function fromRoute($routeName,array $params=array(),$options=array())
    {
        $url = $this->context->getRequest()->getRootPath();
        $path = $this->context->getRouter()->assemble($this->context,$routeName,$params,$options);
        if($path=='/') {
            if($url=='')
                $url = '/';
        } else {
            $url .= $path;
        }
        if(isset($options['query']))
            $url .= '?'.http_build_query($options['query']);
        return $url;
    }

    public function fromPath($path,$options=array())
    {
        $url = $this->context->getRequest()->getRootPath();
        if($path=='/') {
            if($url=='')
                $url = '/';
        } else {
            $url .= $path;
        }
        if(isset($options['query']))
            $url .= '?'.http_build_query($options['query']);
        return $url;
    }

    public function rootPath()
    {
        return $this->context->getRequest()->getRootPath();
    }

    public function prefix()
    {
        return $this->context->getRequest()->getPathPrefix();
    }
}
