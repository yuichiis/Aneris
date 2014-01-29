<?php
namespace Aneris\Annotation\Annotations;

use Aneris\Annotation\DontRegistAnnotationInterface;

/**
 * @Annotation
 */
class Enum implements DontRegistAnnotationInterface
{
	public $value;

	public $hashValue;
}