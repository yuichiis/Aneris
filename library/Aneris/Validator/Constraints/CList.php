<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;

/**
 * The annotated element must be false.
 *
 * @Annotation
 * @Target({ METHOD, FIELD })
 */
class CList extends ConstraintAbstract
{
    public $message = "list of constraint.";
    public $groups = array();
    public $payload = array();

    /**
    * value list of annotation.
    */
    public $value;
}