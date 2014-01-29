<?php
namespace Aneris\Module\Twig\Extension;

use Twig_Extension;
use Twig_Function_Method;
use Aneris\Container\ServiceLocatorInterface;
use Aneris\Form\ElementCollectionInterface;
use Aneris\Form\ElementInterface;
use Aneris\Form\View\FormRenderer;

class Form extends Twig_Extension
{
    const FORM_SERVICE_NAME = 'Aneris\Form\View\FormRendererService';
	protected $renderer;
    protected $theme;
    protected $translator;
    protected $textDomain;

    public function __construct($serviceManagerOrRenderer=null,$theme=null,$translator=null,$textDomain=null)
    {
        $this->serviceManagerOrRenderer = $serviceManagerOrRenderer;
        $this->theme = $theme;
        $this->translator = $translator;
        $this->textDomain = $textDomain;
    }

    public function getRenderer()
    {
    	if($this->renderer)
    		return $this->renderer;
        if($this->serviceManagerOrRenderer) {
            if($this->serviceManagerOrRenderer instanceof FormRenderer) {
                $this->renderer = $this->serviceManagerOrRenderer;
                $this->serviceManagerOrRenderer = null;
            } else {
                $this->renderer = $this->serviceManagerOrRenderer->get(self::FORM_SERVICE_NAME);
            }
        } else {
            $this->renderer = new FormRenderer($this->theme,$this->translator,$this->textDomain);
        }
    	return $this->renderer;
    }

    public function getName()
    {
        return 'form';
    }

    public function getFunctions()
    {
        return array(
            'form_open'   => new Twig_Function_Method($this,'open',  array('is_safe' => array('html'))),
            'form_close'  => new Twig_Function_Method($this,'close', array('is_safe' => array('html'))),
            'form_widget' => new Twig_Function_Method($this,'widget',array('is_safe' => array('html'))),
            'form_label'  => new Twig_Function_Method($this,'label', array('is_safe' => array('html'))),
            'form_errors' => new Twig_Function_Method($this,'errors',array('is_safe' => array('html'))),
            'form_raw'    => new Twig_Function_Method($this,'raw',   array('is_safe' => array('html'))),
            'form_theme'  => new Twig_Function_Method($this,'setTheme',array('is_safe' => array('html'))),
            'form_add'    => new Twig_Function_Method($this,'addElement',array('is_safe' => array('html'))),
        );
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
