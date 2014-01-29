<?php
namespace Aneris\Aop\Annotations;

use Aneris\Stdlib\Entity\PropertyAccessAbstract;

/**
* The annotated method will be used as advice.
*
* @Annotation
* @Target({ METHOD })
*/
class After extends PropertyAccessAbstract
{
	public $value;
}