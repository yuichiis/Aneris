<?php
namespace Aneris\Form;

use ArrayObject;
class ElementSelection extends ElementArrayAbstract implements ElementSelectionInterface
{
	public $name;
	public $type;
	public $value;
	public $label;
	public $attributes;
	public $errors;
	public $multiple;
    public $options;
}