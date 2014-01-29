<?php
namespace Aneris\Form\Element;

use Aneris\Form\ElementCollection;

/**
 * @Annotation
 * @Target({ TYPE })
 */
class Form extends ElementCollection
{
	/**
	 * @Enum({"form"})
	 */
    public $type = 'form';
    
    public $hasErrors;
}