<?php
namespace Aneris\Annotation\Annotations;

use Aneris\Annotation\DontRegistAnnotationInterface;

/**
 * @Annotation
 */
class Target implements DontRegistAnnotationInterface
{
	public $value;

	public $binValue;
}