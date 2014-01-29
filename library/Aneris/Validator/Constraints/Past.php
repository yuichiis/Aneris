<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;
/**
 * The annotated element must be a date in the past.
 *
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE, PARAMETER })
 * @Constraint(validatedBy = {})
 */
class Past extends ConstraintAbstract
{
	public $message = "must be in the past.";
	public $groups = array();
	public $payload = array();
}
