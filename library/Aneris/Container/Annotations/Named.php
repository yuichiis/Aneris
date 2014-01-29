<?php
namespace Aneris\Container\Annotations;

use Aneris\Stdlib\Entity\PropertyAccessAbstract;

/**
* The annotated class is allowed to access by alias from injector.
* And The annotated parameter of injecter is referred to object by 
* alias.
*
* @Annotation
* @Target({ TYPE,FIELD,METHOD,PARAMETER })
*/
class Named extends PropertyAccessAbstract
{
	/**
	* @var string  alias of class 
	*/
	public $value;

	/**
	* @var string  variable name of parameter in a injector method.
	*/
	public $parameter;
}
