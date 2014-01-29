<?php
namespace Aneris\Form\View;

use Aneris\Form\Exception;
use Aneris\Form\ElementInterface;
use Aneris\Form\ElementSelectionInterface;
use Aneris\Form\ElementCollectionInterface;
use Aneris\Form\ElementCollection;
use Aneris\Form\Element;

class FormRenderer
{
    protected static $needToTranslate = array(
        'value' => array(
            'submit' => true,
            'reset'  => true,
            'button' => true,
        ),
        'placeholder' => array(
            'text'     => true,
            'password' => true,
            'search'   => true,
            'url'      => true,
            'email'    => true,
            'tel'      => true,
            'textarea' => true,
        ),
        'alt' => array(
            'image' => true,
        ),
    );

    protected $translator;
    protected $textDomain;
    protected $themes;
    protected $theme;

    public function __construct(array $themes=null,$translator=null,$textDomain=null)
    {
        $this->themes = $themes;
        if(isset($themes['default']))
            $this->theme = 'default';
        $this->translator = $translator;
        $this->textDomain = $textDomain;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    public function __invoke()
    {
        return $this->invoke();
    }

    public function invoke()
    {
        return $this;
    }

    protected function escape($value)
    {
        return htmlspecialchars($value,ENT_COMPAT,'UTF-8');
    }

    protected function translate($value)
    {
        if($this->translator) {
            if($this->textDomain) {
                return $this->translator->translate($value,$this->textDomain);
            } else {
                return $this->translator->translate($value);
            }
        }
        return $value;
    }

    public function getTheme()
    {
        if($this->theme==null)
            return null;
        if(is_array($this->theme))
            return $this->theme;
        if(is_string($this->theme)) {
            if($this->themes && isset($this->themes[$this->theme])) {
                $this->theme = $this->getThemeFormClass($this->themes[$this->theme]);
                return $this->theme;
            }
            $this->theme = $this->getThemeFormClass($this->theme);
            return $this->theme;
        }
        throw new Exception\DomainException('Theme is invalid type.:',gettype($this->theme));
    }

    protected function getThemeFormClass($themeClass)
    {
        if(!class_exists($themeClass) || !property_exists($themeClass, 'config'))
            throw new Exception\DomainException('Theme\'s class is not exist.: '.$themeClass);
        return $themeClass::$config;
    }

    public function open(ElementCollectionInterface $element,array $attributes=array())
    {
        if($attributes===array()) {
            $theme = $this->getTheme();
            if(isset($theme['widget'])) {
                if(isset($theme['widget'][$element->type]))
                    $attributes = $theme['widget'][$element->type];
                else if(isset($theme['widget']['default']))
                    $attributes = $theme['widget']['default'];
            }
        }
        $html = '<'.$element->type;
        if($element->attributes) {
            foreach($element->attributes as $name => $value) {
                if(!isset($attributes[$name]))
                    $attributes[$name] = $value;
            }
        }
        foreach($attributes as $name => $value) {
            $html .= ' '.$name.'="'.$this->escape($value).'"';
        }
        $html .= ">\n";
        return $html;
    }

    public function close(ElementCollectionInterface $element,array $attributes=array())
    {
        return '</'.$element->type.">\n";
    }

    public function label(ElementInterface $element,array $attributes=array())
    {
        if($element->label===null)
            return '';
        $html = '<label';
        if(!isset($attributes['for'])) {
            if(isset($element->attributes['id']))
                $attributes['for'] = $element->attributes['id'];
        }
        if(!isset($attributes['accesskey'])) {
            if(isset($element->attributes['accesskey']))
                $attributes['accesskey'] = $element->attributes['accesskey'];
        }
        if($element instanceof ElementSelectionInterface &&
            ($element->type=='radio'|| $element->type=='checkbox'))
            unset($attributes['for']);
        foreach($attributes as $name => $value) {
            $html .= ' '.$name.'="'.$this->escape($value).'"';
        }
        $html .= '>'.$this->escape($this->translate($element->label))."</label>\n";
        return $html;
    }

    public function widget(ElementInterface $element,array $attributes=array())
    {
        if(isset($attributes['type']))
            $type = $attributes['type'];
        else
            $type = $element->type;
        if($element instanceof ElementSelectionInterface) {
            if($element->type=='select')
                return $this->widgetSelect($element,$attributes);
            else
                return $this->widgetRadio($element,$attributes);
        } else if($element instanceof ElementCollectionInterface) {
            return $this->widgetCollection($element,$attributes);
        }

        if($type=='option') {
            $attributes['type'] = null;
            return $this->widgetOption($element,$attributes);
        } else if($type=='textarea') {
            return $this->widgetTextarea($element,$attributes);
        }
        return $this->widgetInput($element,$attributes);
        throw new Exception\DomainException('Unknown Element Type: '.$type);
    }

    public function errors(ElementInterface $element,array $attributes=array())
    {
        if($element->errors==null)
            return '';
        $html = '';
        foreach($element->errors as $error) {
            $html .= '<small';
            foreach($attributes as $name => $value) {
                $html .= ' '.$name.'="'.$this->escape($value).'"';
            }
            $html .= '>'.$this->escape($this->translate($error))."</small>\n";
        }
        return $html;
    }

    public function raw(ElementInterface $element,$attributes=array())
    {
        if($element instanceof ElementCollectionInterface) {
            return $this->widgetCollection($element,$attributes);
        }

        $fieldDivAttr = null;
        $labelDivAttr = null;
        $widgetDivAttr = null;
        $labelAttr = array();
        $errorAttr = array();
        if($attributes===array()) {
            $theme = $this->getTheme();
            if(isset($theme['field'])) {
                if($element->errors!==null && count($element->errors)!=0)
                    $status = 'error';
                else
                    $status = 'success';
                if(isset($theme['field'][$status]['field']))
                    $fieldDivAttr = $theme['field'][$status]['field'];
                if(isset($theme['field'][$status]['label']))
                    $labelDivAttr = $theme['field'][$status]['label'];
                if(isset($theme['field'][$status]['widget']))
                    $widgetDivAttr = $theme['field'][$status]['widget'];
            }
            if(isset($theme['label'])) {
                if(isset($theme['label'][$element->type]))
                    $labelAttr = $theme['label'][$element->type];
                else if(isset($theme['label']['default']))
                    $labelAttr = $theme['label']['default'];
            }
            if(isset($theme['widget'])) {
                if(isset($theme['widget'][$element->type]))
                    $attributes = $theme['widget'][$element->type];
                else if(isset($theme['widget']['default']))
                    $attributes = $theme['widget']['default'];
            }
            if(isset($theme['errors']))
                $errorAttr = $theme['errors'];
        }
        if(isset($attributes['fieldDivClass'])) {
            $fieldDivAttr['class'] = $attributes['fieldDivClass'];
            unset($attributes['fieldDivClass']);
        }
        if(isset($attributes['labelDivClass'])) {
            $labelDivAttr['class'] = $attributes['labelDivClass'];
            unset($attributes['labelDivClass']);
        }
        if(isset($attributes['widgetDivClass'])) {
            $widgetDivAttr['class'] = $attributes['widgetDivClass'];
            unset($attributes['widgetDivClass']);
        }
        if(isset($attributes['labelClass'])) {
            $labelAttr['class'] = $attributes['labelClass'];
            unset($attributes['labelClass']);
        }
        if(isset($attributes['errorClass'])) {
            $errorAttr['class'] = $attributes['errorClass'];
            unset($attributes['errorClass']);
        }
        if(isset($attributes['id'])) {
            $labelAttr['for'] = $attributes['id'];
        }

        $html = $this->openDiv($fieldDivAttr).
                $this->openDiv($labelDivAttr).
                $this->label($element,$labelAttr).
                $this->closeDiv($labelDivAttr).
                $this->openDiv($widgetDivAttr).
                $this->widget($element,$attributes).
                $this->errors($element,$errorAttr).
                $this->closeDiv($widgetDivAttr).
                $this->closeDiv($fieldDivAttr);
        return $html;
    }

    protected function openDiv($attributes)
    {
        $html = '';
        if($attributes!==null) {
            $html .= '<div';
            foreach($attributes as $name => $value) {
                if($name!='class' || $value!==true)
                    $html .= ' '.$name.'="'.$this->escape($value).'"';
            }
            $html .= ">\n";
        }
        return $html;
    }

    protected function closeDiv($attributes)
    {
        $html = '';
        if($attributes!==null) {
            $html = "</div>\n";
        }
        return $html;
    }

    protected function widgetCollection(ElementCollectionInterface $elementCollection,array $attributes=array())
    {
        $html = $this->open($elementCollection,$attributes);
        foreach($elementCollection as $element) {
            $html .= $this->raw($element);
        }
        $html .= $this->close($elementCollection,$attributes);
        return $html;
    }

    protected function widgetOption(ElementInterface $element,array $attributes=array())
    {
        if(!array_key_exists('value', $attributes) && $element->value)
            $attributes['value'] = $element->value;
        $html = $this->openTag('option',$element,$attributes);
        if($element->label)
            $html .= $this->escape($this->translate($element->label))."\n";
        $html .= $this->closeTag('option',$element);
        return $html;
    }

    protected function widgetTextarea(ElementInterface $element,array $attributes=array())
    {
        $html = $this->openTag('textarea',$element,$attributes);
        if($element->value)
            $html .= $this->escape($element->value)."\n";
        $html .= $this->closeTag('textarea',$element);
        return $html;
    }

    protected function widgetInput(ElementInterface $element,array $attributes=array())
    {
        if(!array_key_exists('type', $attributes) && $element->type)
            $attributes['type'] = $element->type;
        if(!array_key_exists('value', $attributes) && $element->value)
            $attributes['value'] = $element->value;
        return $this->openTag('input',$element,$attributes);
    }

    public function widgetRadio(ElementSelectionInterface $element,array $attributes=array())
    {
        $html = '';
        $isMultiple = false;
        $attributes['name'] = $element->name;
        if($element->multiple || $element->type=='checkbox') {
            $attributes['name'] = $element->name.'[]';
            $isMultiple = true;
            if($element->value!==null) {
                if(is_array($element->value))
                    $tmp = $element->value;
                else
                    $tmp = array($element->value);
                foreach($tmp as $value) {
                    $values[$value] = true;
                }
            }
        }
        $attributes['type'] = $element->type;
        $divClass = null;
        if(isset($attributes['itemDivClass'])) {
            $divClass = $attributes['itemDivClass'];
            unset($attributes['itemDivClass']);
        }
        $labelClass = '';
        if(isset($attributes['itemLabelClass'])) {
            $labelClass = ' class="'.$attributes['itemLabelClass'].'"';
            unset($attributes['itemLabelClass']);
        }
        $outOfLabel = false;
        if(isset($attributes['outOfLabel'])) {
            
            $outOfLabel = $attributes['outOfLabel'];
            unset($attributes['outOfLabel']);
        }
        unset($attributes['id']);
        foreach($element as $el) {
            if($isMultiple)
                $selected = isset($values[$el->value]);
            else
                $selected = ($el->value == $element->value);
            if($divClass)
                $html .= '<div class="'.$divClass.'">'."\n";
            if(isset($el->attributes['id']))
                $for = ' for="'.$el->attributes['id'].'"';
            else
                $for = '';
            if(isset($el->attributes['accesskey']))
                $accesskey = ' accesskey="'.$el->attributes['accesskey'].'"';
            else
                $accesskey = '';
            if($el->label && !$outOfLabel) {
                $html .= '<label'.$labelClass.$for.$accesskey.'>'."\n";
            }
            $attr = $attributes;
            if($selected)
                $attr['checked'] = 'checked';
            $html .= $this->widget($el,$attr);
            if($el->label && $outOfLabel) {
                $html .= '<label'.$labelClass.$for.'>'."\n";
            }
            if($el->label)
                $html .= $this->escape($this->translate($el->label))."\n</label>\n";
            if($divClass)
                $html .= "</div>\n";
        }
        return $html;
    }

    protected function widgetSelect(ElementSelectionInterface $element,array $attributes=array())
    {
        $attributes['value'] = null;
        $isMultiple = false;
        if($element->multiple) {
            $attributes['name'] = $element->name.'[]';
            $attributes['multiple'] = true;
            $isMultiple = true;
            if($element->value!==null) {
                if(is_array($element->value))
                    $tmp = $element->value;
                else
                    $tmp = array($element->value);
                foreach($tmp as $value) {
                    $values[$value] = true;
                }
            }
        }
        $html = $this->openTag($element->type,$element,$attributes);
        foreach($element as $el) {
            if($isMultiple)
                $selected = isset($values[$el->value]);
            else
                $selected = ($el->value == $element->value);
            if($selected)
                $attr = array('name'=>null,'selected'=>'selected');
            else
                $attr = array('name'=>null);
            $html .= $this->widgetOption($el,$attr);
        }
        $html .= $this->closeTag($element->type,$element,$attributes);
        return $html;
    }

    public function openTag($tag,ElementInterface $element,array $attributes=array())
    {
        if($element->type!==null)
            $type = $element->type;
        else if(isset($attributes['type']))
            $type = $attributes['type'];
        else
            $type = $tag;

        $html = '<'.$tag;
        if($element->attributes) {
            foreach($element->attributes as $name => $value) {
                if(!isset($attributes[$name]))
                    $attributes[$name] = $value;
            }
        }
        if(!array_key_exists('name', $attributes) && $element->name)
            $attributes['name'] = $element->name;

        // for a label
        unset($attributes['for']);
        unset($attributes['accesskey']);

        foreach($attributes as $name => $value) {
            if($value!==null) {
                if(isset(self::$needToTranslate[$name][$type])) {
                    $html .= ' '.$name.'="'.$this->escape($this->translate($value)).'"';
                } else {
                    if($value===true)
                        $html .= ' '.$name;
                    else
                        $html .= ' '.$name.'="'.$this->escape($value).'"';
                }
            }
        }
        $html .= ">\n";
        return $html;
    }

    public function closeTag($tag,ElementInterface $element,array $attributes=array())
    {
        return '</'.$tag.">\n";
    }

    public function addElement(ElementCollectionInterface $elementCollection,$type,$name,$value=null,$label=null)
    {
        if($type=='radio'||$type=='checkbox'||$type=='select')
            $element = new ElementSelection();
        else
            $element = new Element();
        $element->type = $type;
        $element->name = $name;
        $element->value = $value;
        $element->label = $label;
        $elementCollection[$name] = $element;
    }
}
