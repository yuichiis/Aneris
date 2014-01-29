<?php
namespace Aneris\Form;

use Aneris\Annotation\AnnotationManager;
use Aneris\Validator\Validator;
use Aneris\Stdlib\Cache\CacheFactory;

use ReflectionClass;

class FormContextBuilder
{
    protected $cachePath;
    protected $formCache;
    protected $annotationReader;
    protected $validator;
    protected $hydrator;

    public function __construct($annotationReader=null,$validator=null,$hydrator=null)
    {
        if($annotationReader) {
            $this->annotationReader = $annotationReader;
        } else {
            $this->annotationReader = AnnotationManager::factory();
        }
        if($validator) {
            $this->validator = $validator;
        } else {
            $this->validator = new Validator();
            if($annotationReader)
                $this->validator->getConstraintManager()
                    ->setAnnotationReader($annotationReader);
        }
        $this->hydrator = $hydrator;
    }

    public function setCachePath($path)
    {
        $this->cachePath = $path;
        return $this;
    }

    public function getFormCache()
    {
        if($this->formCache)
            return $this->formCache;

        if($this->cachePath)
            $path = $this->cachePath;
        else
            $path = '/'.__CLASS__;

        $this->formCache = CacheFactory::getInstance($path);
        return $this->formCache;
    }

    public function getAnnotationReader()
    {
        return $this->annotationReader;
    }

    public function setAnnotationReader($annotationReader)
    {
        $this->annotationReader = $annotationReader;
        return $this;
    }

    public function addNameSpace($nameSpace)
    {
        $this->annotationReader->addNameSpace($nameSpace);
    }

	public function build($className)
	{
        $binding = null;
        if(is_object($className)) {
            $binding = $className;
            $className = get_class($className);
        }
        $formCache = $this->getFormCache();
        if(isset($formCache[$className]))
            return new FormContext(
                $this->cloneForm($formCache[$className]),
                $binding,
                $this->validator,
                $this->hydrator);

        $reader = $this->annotationReader;
        $classRef = new ReflectionClass($className);
        $annotations = $reader->getClassAnnotations($classRef);
        $form = null;
        foreach ($annotations as $annotation) {
            if($annotation instanceof Element\Form) {
                $form = clone $annotation;
                break;
            }
        }
        if($form==null)
        	throw new Exception\DomainException('@Form Annotaion is not found:'.$className);

        foreach($classRef->getProperties() as $ref) {
            $annotations = $reader->getPropertyAnnotations($ref);
            if(count($annotations)==0)
                continue;
            foreach ($annotations as $annotation) {
                if($annotation instanceof ElementInterface) {
                    $element = clone $annotation;
                    if($element->name) {
                    	$name = $element->name;
                    } else {
                    	$name = $ref->getName();
                    	$element->name = $name;
                    }
                    $form[$name] = $element;
                    break;
                }
            }
        }
        $formCache[$className] = $form;
        return new FormContext(
            $this->cloneForm($form),
            $binding,
            $this->validator,
            $this->hydrator);
	}

    public function cloneForm($form)
    {
        return clone $form;
    }
}