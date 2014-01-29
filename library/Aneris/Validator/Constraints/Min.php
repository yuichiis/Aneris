<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;
/**
 * The annotated element must be a number whose value must be higher or
 * equal to the specified minimum.
 *
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE , PARAMETER})
 * @Constraint(validatedBy = {})
 */
class Min extends ConstraintAbstract
{
    public $message = "must be greater than or equal to {value}.";
    public $groups = array();
    public $payload = array();

    /**
    * value the element must be higher or equal to
    */
    public $value;
}
