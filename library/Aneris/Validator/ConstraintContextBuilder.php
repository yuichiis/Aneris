<?php
namespace Aneris\Validator;

use Aneris\Annotation\AnnotationManager;
use Aneris\Stdlib\Cache\CacheFactory;

use ReflectionClass;
use Aneris\Annotation\ElementType;
use Aneris\Annotation\AnnotationMetaData;
use Aneris\Validator\Constraints\CList;

class ConstraintContextBuilder implements ConstraintManagerInterface
{
    protected $cachePath;
    protected $constraintsCache;
    protected $annotationReader;

    public function __construct($constraintValidatorFactory=null,$annotationReader=null)
    {
        if($constraintValidatorFactory)
            $this->constraintValidatorFactory = $constraintValidatorFactory;
        else
            $this->constraintValidatorFactory = new ConstraintValidatorFactory();

        if($annotationReader)
            $this->annotationReader = $annotationReader;
        else
            $this->annotationReader = AnnotationManager::factory();
    }

    public function setCachePath($path)
    {
        $this->cachePath = $path;
        return $this;
    }

    public function getConstraintsCache()
    {
        if($this->constraintsCache)
            return $this->constraintsCache;

        if($this->cachePath)
            $path = $this->cachePath;
        else
            $path = '/'.__CLASS__;

        $this->constraintsCache = CacheFactory::getInstance($path);
        return $this->constraintsCache;
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

    public function getConstraints($className)
    {
        $constraintsCache = $this->getConstraintsCache();
        if(isset($constraintsCache[$className]))
            return $constraintsCache[$className];

        $reader = $this->annotationReader;
        $constraints = array();
        $classRef = new ReflectionClass($className);
        $annotations = $reader->getClassAnnotations($classRef);
        foreach ($annotations as $annotation) {
            if($annotation instanceof ConstraintInterface)
                $constraints['__CLASS__'][get_class($annotation)] = $annotation;
        }
        $this->getConstraintsByTarget(
            $constraints,
            $classRef->getProperties(),
            ElementType::FIELD
        );
        $this->getConstraintsByTarget(
            $constraints,
            $classRef->getMethods(),
            ElementType::METHOD
        );

        $constraintsCache[$className] = $constraints;
        return $constraints;
    }

    protected function getConstraintsByTarget(&$constraints,$reflections,$elementType)
    {
        $reader = $this->annotationReader;
        foreach($reflections as $ref) {
            if($elementType==ElementType::FIELD) {
                $annotations = $reader->getPropertyAnnotations($ref);
            }
            else if($elementType==ElementType::METHOD) {
                $annotations = $reader->getMethodAnnotations($ref);
            }
            if(count($annotations)==0)
                continue;
            $constraintList = array();
            foreach ($annotations as $annotation) {
                if($annotation instanceof ConstraintInterface)
                    $constraintList[] = $annotation;
            }
            $constraints[$ref->getName()] = $this->getConstraintValidators($constraintList);
        }
    }

    protected function getConstraintValidators(array $constraintList,$parentConstaint=null)
    {
        $constraintContextList = array();
        foreach($constraintList as $constraint) {
            if($constraint instanceof CList) {
                $children = $constraint->value;
                $constraintContextList = array_merge(
                    $constraintContextList,
                    $this->getConstraintValidators($children));
                continue;
            }

            $children = $this->getChildrenConstraint($constraint);
            if($children) {
                $constraintContextList = array_merge(
                    $constraintContextList,
                    $this->getConstraintValidators($children,$constraint));
            }
            $constraintContext = new \stdClass();
            if($parentConstaint)
                $constraint->groups = $parentConstaint->groups;
            $constraintContext->constraint = $constraint;
            $constraintContext->validator = $this->getConstraintValidator($constraint);
            $constraintContextList[] = $constraintContext;
        }
        return $constraintContextList;
    }

    protected function getConstraintValidator($constraint)
    {
        if(isset($constraint->validatedBy))
            $validatorName = $constraint->validatedBy;
        else
            $validatorName = get_class($constraint).'Validator';
        $validator = $this->constraintValidatorFactory->newInstance($validatorName);
        $validator->initialize($constraint);
        return $validator;
    }

    public function getChildrenConstraint($constraint)
    {
        $reader = $this->annotationReader;
        if(method_exists($reader,'getMetaData'))
            $metaData = $reader->getMetaData($constraint);
        else
            $metaData = $this->generatetMetaData($constraint);

        if($metaData===false)
            return false;
        if($metaData->classAnnotations==null)
            return false;
        $children = array();
        foreach ($metaData->classAnnotations as $annotation) {
            if(!($annotation instanceof ConstraintInterface))
                continue;
            $children[] = clone $annotation;
        }
        if(count($children))
            return $children;
        else
            return false;
    }

    public function generatetMetaData($constraint)
    {
        $reader = $this->annotationReader;
        $ref = new ReflectionClass($constraint);
        $metaData = new AnnotationMetaData();
        $metaData->classAnnotations = $reader->getClassAnnotations($ref);
        return $metaData;
    }
}