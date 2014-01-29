<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;

/**
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE, PARAMETER })
 * @Constraint(validatedBy = {})
 */
class Digits extends ConstraintAbstract
{
    public $message = "numeric value out of bounds (<{integer} digits>.<{fraction} digits> expected)";
    public $groups  = array();
    public $payload = array();

    /**
    * maximum number of integral digits accepted for this number.
    */
    public $integer;

    /**
    * maximum number of fractional digits accepted for this number.
    */
    public $fraction;
}
