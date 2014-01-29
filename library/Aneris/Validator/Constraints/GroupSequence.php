<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;
/**
 * Define a group sequence
 *
 * @Annotation
 * @Target({ TYPE })
 * @Retention(RUNTIME)
 */
class GroupSequence extends ConstraintAbstract
{
	/**
	 * Class<?>[] value();
     */
	public $value;

	public function __construct(array $groups=null)
	{
		if(isset($groups['value']))
			$this->value = $groups['value'];
		else
			$this->value = $groups;
	}
}