<?php
namespace Aneris\Mvc\Annotations;

use Aneris\Mvc\AnnotationInterface;
use Aneris\Stdlib\Entity\PropertyAccessAbstract;

/**
 * @Annotation
 * @Target({ TYPE })
 */
class Controller extends PropertyAccessAbstract implements AnnotationInterface
{
}