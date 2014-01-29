<?php
namespace Aneris\Module\Twig\Extension;

use Twig_Extension;
use Twig_Function_Method;
use Aneris\Container\ServiceLocatorInterface;
use Aneris\Mvc\Router as MvcRouter;
use Aneris\Module\Twig\Exception;

class Url extends Twig_Extension
{
    protected $pluginManager;
    protected $url;

    public function __construct($pluginManager=null)
    {
        $this->pluginManager = $pluginManager;
    }

    protected function getUrl()
    {
        if($this->url)
            return $this->url;
        if($this->pluginManager==null)
            throw new Exception\DomainException("plugin manager is not specified.");
        if(!$this->pluginManager->has('Context'))
            throw new Exception\DomainException("context is not specified.");
        $context = $this->pluginManager->get('Context');
        return $this->url = $context->url();
    }

    public function getName()
    {
        return 'url';
    }

    public function getFunctions()
    {
        return array(
            'url'          => new Twig_Function_Method($this,'fromRoute', array('is_safe' => array('html'))),
            'url_frompath' => new Twig_Function_Method($this,'fromPath',  array('is_safe' => array('html'))),
            'url_root'     => new Twig_Function_Method($this,'rootPath',  array('is_safe' => array('html'))),
            'url_prefix'   => new Twig_Function_Method($this,'prefix',    array('is_safe' => array('html'))),
        );
    }

    public function fromRoute($routeName,array $params=array(),$options=array())
    {
        return $this->getUrl()->fromRoute($routeName,$params,$options);
    }

    public function fromPath($path,$options=array())
    {
        return $this->getUrl()->fromPath($path,$options);
    }

    public function rootPath()
    {
        return $this->getUrl()->rootPath();
    }

    public function prefix()
    {
        return $this->getUrl()->prefix();
    }
}
