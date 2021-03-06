<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;

/**
 * The annotated element size must be between the specified boundaries (included).
 *
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE , PARAMETER})
 */
class Size extends ConstraintAbstract
{
    public $message = "size must be between {min} and {max}.";
    public $groups = array();
    public $payload = array();

    /**
    * size the element must be higher or equal to
    */
    public $min;

    /**
    * size the element must be lower or equal to
    */
    public $max;
}
