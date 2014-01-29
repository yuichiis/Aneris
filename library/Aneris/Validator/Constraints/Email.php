<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;

/**
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE , PARAMETER})
 * @Constraint(validatedBy = {})
 */
class Email extends ConstraintAbstract
{
    public $message = "not a well-formed email address.";
    public $groups  = array();
    public $payload = array();

    /**
     * value the element must be email address format
     */
    public $value;
}
