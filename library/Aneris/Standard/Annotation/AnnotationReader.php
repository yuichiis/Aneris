<?php
namespace Aneris\Annotation;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

interface AnnotationReader
{
    public function getClassAnnotations(ReflectionClass $class);
    public function getClassAnnotation(ReflectionClass $class, $annotationName);
    public function getMethodAnnotations(ReflectionMethod $method);
    public function getMethodAnnotation(ReflectionMethod $method, $annotationName);
    public function getPropertyAnnotations(ReflectionProperty $property);
    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName);
}