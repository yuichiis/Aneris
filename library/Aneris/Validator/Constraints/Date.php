<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;
/**
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE ,PARAMETER})
 * @Constraint(validatedBy = {})
 */
class Date extends ConstraintAbstract
{
    public $message = "must be a valid date.";
    public $groups = array();
    public $payload = array();

    /**
     * value the element must be lower or equal to
     */
    public $format;
}

