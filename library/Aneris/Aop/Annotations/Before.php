<?php
namespace Aneris\Aop\Annotations;

use Aneris\Stdlib\Entity\PropertyAccessAbstract;

/**
* The annotated method be used as advice.
*
* @Annotation
* @Target({ METHOD })
*/
class Before extends PropertyAccessAbstract
{
	public $value;
}