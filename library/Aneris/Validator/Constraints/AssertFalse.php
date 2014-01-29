<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;

/**
 * The annotated element must be false.
 *
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE , PARAMETER})
 * @Constraint(validatedBy = {})
 */
class AssertFalse extends ConstraintAbstract
{
    public $message = "must be false.";
    public $groups = array();
    public $payload = array();
}
