<?php
namespace Aneris\Form\Plugin;

use Aneris\Form\ElementInterface;
use Aneris\Form\ElementCollectionInterface;

class Form
{
    const FORM_BUILDER_SERVICE  = 'Aneris\Form\FormContextBuilder';
    const FORM_RENDERER_SERVICE = 'Aneris\Form\View\FormRenderer';

    protected $context;
    protected $renderer;

    public static function factory($pluginManager)
    {
        return new self($pluginManager);
    }

    public function __construct($pluginManager)
    {
        $this->context = $pluginManager->get('Context');
        return $this;
    }

    protected function getRenderer()
    {
        if($this->renderer)
            return $this->renderer;
        return $this->renderer = $this->context->getServiceLocator()->get(self::FORM_RENDERER_SERVICE);
    }

    public function build($class,$method=null,$setData=null)
    {
        $builder = $this->context->getServiceLocator()->get(self::FORM_BUILDER_SERVICE);
        $formContext = $builder->build($class);
        if($method==null) {
            if($this->context->getRequest()->isPost())
                $method = 'post';
            else
                $method = 'entity';
        } else {
            $method = strtolower($method);
        }
        if($method==='post' && $this->context->getRequest()->isPost()) {
            $formContext->setData($this->context->getRequest()->getPosts());
        } else if($method==='get' && !$this->context->getRequest()->isPost()) {
            $formContext->setData($this->context->getRequest()->getQueries());
        } else if($method==='entity') {
            if($setData==null)
                $formContext->setData($class);
            else
                $formContext->setData($setData);
        }
        return $formContext;
    }

    public function open(ElementCollectionInterface $element,array $attributes=array())
    {
        return $this->getRenderer()->open($element,$attributes);
    }

    public function close(ElementCollectionInterface $element,array $attributes=array())
    {
        return $this->getRenderer()->close($element,$attributes);
    }

    public function label(ElementInterface $element,array $attributes=array())
    {
        return $this->getRenderer()->label($element,$attributes);
    }

    public function widget(ElementInterface $element,array $attributes=array())
    {
        return $this->getRenderer()->widget($element,$attributes);
    }

    public function errors(ElementInterface $element,array $attributes=array())
    {
        return $this->getRenderer()->errors($element,$attributes);
    }

    public function raw(ElementInterface $element,$attributes=array())
    {
        return $this->getRenderer()->raw($element,$attributes);
    }

    public function setTheme($theme)
    {
        return $this->getRenderer()->setTheme($theme);
    }

    public function addElement(ElementCollectionInterface $elementCollection,$type,$name,$value=null,$label=null)
    {
        return $this->getRenderer()->addElement($elementCollection,$type,$name,$value,$label);
    }
}
