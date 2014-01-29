<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;
/**
 * The annotated element must not be <code>null</code>.
 * Accepts any type.
 *
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE })
 * @Constraint(validatedBy = {})
 */
class NotNull extends ConstraintAbstract
{
    public $message = "may not be null.";
    public $groups = array();
    public $payload = array();
}
