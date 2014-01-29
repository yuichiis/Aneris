<?php
namespace Aneris\Annotation;

class AnnotationMetaData
{
	/** @var string **/
	public $className;

	/** @var bool **/
	public $hasConstructor;

	/** @var array<Annotaion> **/
	public $classAnnotations;

	/** @var array<Annotaion> **/
	public $fieldAnnotations;

	/** @var array<Annotaion> **/
	public $methodAnnotations;
}