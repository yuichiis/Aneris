<?php
namespace Aneris\Aop\Annotations;

use Aneris\Stdlib\Entity\PropertyAccessAbstract;

/**
* The annotated method be used as advice.
*
* @Annotation
* @Target({ METHOD })
*/
class AfterThrowing extends PropertyAccessAbstract
{
	public $value;
}