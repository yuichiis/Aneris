<?php
namespace Aneris\Form\Element;

use Aneris\Form\Element;

/**
 * @Annotation
 * @Target({ FIELD })
 */
class Input extends Element
{
	/**
	 * @Enum({
	 *   "text","password","file","hidden","submit","reset","button","image",
	 *   "search","tel","url","email","number","range","color",
	 *   "datetime","date","month","week","time","datetime-local"
	 * })
	 */
    public $type = 'text';
}