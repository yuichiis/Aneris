<?php
namespace Aneris\Validator;

interface ConstraintInterface
{
    /**
     * ElementTypes:
     *     FIELD for constrained attributes
     *     METHOD for constrained getters
     *     TYPE for constrained beans
     *     ANNOTATION_TYPE for constraints composing other constraints
     * Built-in Types:
     *     PARAMETER
     *     CONSTRUCTOR
     */
    const FIELD           = 'FIELD';
    const METHOD          = 'METHOD';
    const TYPE            = 'TYPE';
    const ANNOTATION_TYPE = 'ANNOTATION_TYPE';
    const PARAMETER       = 'PARAMETER';
    const CONSTRUCTOR     = 'CONSTRUCTOR';
}