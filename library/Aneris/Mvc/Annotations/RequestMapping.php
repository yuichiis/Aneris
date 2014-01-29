<?php
namespace Aneris\Mvc\Annotations;

use Aneris\Mvc\AnnotationInterface;
use Aneris\Stdlib\Entity\PropertyAccessAbstract;

/**
 * @Annotation
 * @Target({ TYPE,METHOD })
 */
class RequestMapping extends PropertyAccessAbstract implements AnnotationInterface
{
	public $value;
	public $method;
	public $view;
	public $headers;
	public $ns;
	public $parameters;
	public $name;
}