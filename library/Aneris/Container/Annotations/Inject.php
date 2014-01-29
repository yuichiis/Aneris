<?php
namespace Aneris\Container\Annotations;

use Aneris\Stdlib\Entity\PropertyAccessAbstract;

/**
* The annotated method is used as injector.
*
* @Annotation
* @Target({ FIELD,METHOD })
*/
class Inject extends PropertyAccessAbstract
{
	/**
	* @var array  list of pair that is including a variable name and a reference for parmeters. 
	*/
	public $value;
}