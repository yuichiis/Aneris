<?php
namespace Aneris\Form;

use Aneris\Stdlib\Entity\PropertyAccessAbstract;

class Element extends PropertyAccessAbstract implements ElementInterface
{
	public $name;
	public $type;
	public $value;
	public $label;
	public $attributes;
	public $errors;
}