<?php
namespace Aneris\Container\Annotations;

use Aneris\Stdlib\Entity\PropertyAccessAbstract;

/**
* The annotated method will be called after injection process.
*
* @Annotation
* @Target({ METHOD })
*/
class PostConstruct extends PropertyAccessAbstract
{
}