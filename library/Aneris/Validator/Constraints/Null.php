<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;
/**
 * The annotated element must be <code>null</code>.
 * Accepts any type.
 *
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE })
 * @Constraint(validatedBy = {})
 */
class Null extends ConstraintAbstract
{
    public $message = "must be null.";
    public $groups = array();
    public $payload = array();
}
