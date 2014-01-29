<?php
namespace Aneris\Form\Element;

use Aneris\Form\ElementSelection;

/**
 * @Annotation
 * @Target({ FIELD })
 */
class Select extends ElementSelection
{
	/**
	 * @Enum({"select","radio","checkbox"})
	 */
    public $type = 'select';

    public $options;
}