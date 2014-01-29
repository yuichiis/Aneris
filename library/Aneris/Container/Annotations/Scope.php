<?php
namespace Aneris\Container\Annotations;

use Aneris\Stdlib\Entity\PropertyAccessAbstract;

/**
* The annotated class will be created to instance as defined lifecycle.
*
* @Annotation
* @Target({ TYPE })
*/
class Scope extends PropertyAccessAbstract
{
	/**
	* @Enum("singleton","prototype","request","session","global_session")
	*/
	public $value;
}