<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;
/**
 * The annotated element must be a date in the future.
 *
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE, CONSTRUCTOR, PARAMETER })
 * @Constraint(validatedBy = {})
 */
class Future extends ConstraintAbstract
{
    public $message = "must be in the future.";
    public $groups = array();
    public $payload = array();
}
