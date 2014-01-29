<?php
namespace Aneris\Container\Annotations;

use Aneris\Stdlib\Entity\PropertyAccessAbstract;

/**
* it will be used annotated mode when it create a instance with proxy.
*
* @Annotation
* @Target({ TYPE })
*/
class Proxy extends PropertyAccessAbstract
{
	public $value = true;
}