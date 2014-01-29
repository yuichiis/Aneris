<?php
namespace Aneris\Annotation;

interface ElementType
{
    const TYPE            = 'TYPE';
    const FIELD           = 'FIELD';
    const METHOD          = 'METHOD';
	const ANNOTATION_TYPE = 'ANNOTATION_TYPE';

    // reserved
	const CONSTRUCTOR     = 'CONSTRUCTOR';
    const PACKAGE         = 'PACKAGE';
	const LOCAL_VARIABLE  = 'LOCAL_VARIABLE';
    const PARAMETER       = 'PARAMETER';
}
