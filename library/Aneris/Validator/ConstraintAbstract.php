<?php
namespace Aneris\Validator;

use Aneris\Stdlib\Entity\PropertyAccessAbstract;

abstract class ConstraintAbstract extends PropertyAccessAbstract implements ConstraintInterface
{
    public $validatedBy;
    public $path;

    public function initialize(ConstraintInterface $constraint)
    {
    }
}