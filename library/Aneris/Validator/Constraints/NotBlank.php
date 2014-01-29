<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;

/**
 * The annotated element must be false.
 * Supported types are <code>boolean</code> and <code>Boolean</code>
 *
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE , PARAMETER})
 * @Constraint(validatedBy = {})
 */
class NotBlank extends ConstraintAbstract
{
    public $message = "may not be blank.";
    public $groups = array();
    public $payload = array();
}

