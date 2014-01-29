<?php
namespace Aneris\Validator\Constraints;

use Aneris\Validator\ConstraintAbstract;

/**
* The annotated String must match the following regular expression.
*
* @Annotation
* @Target({ METHOD, FIELD, ANNOTATION_TYPE, PARAMETER })
* @Constraint(validatedBy = {})
*/
class Pattern extends ConstraintAbstract
{
    /**
    * The regular expression to match.
    */
    public $regexp;

    /**
    * Array of <code>Flag</code>s considered when resolving the regular expression.
    */
    public $flags = array();

    /**
    * The error message template.
    */
    public $message = 'must match "{regexp}"';

    /**
    * The groups the constraint belongs to.
    */
    public $groups = array();

    /**
    * The payload associated to the constraint
    */
    public $payload = array();

    /**
    * Enables case-insensitive matching
    */
    const CASE_INSENSITIVE = 'CASE_INSENSITIVE';

    /**
    * Enables multiline mode
    */
    const MULTILINE = 'MULTILINE';

    /**
    * Enables dotall mode
    */
    const DOTALL = 'DOTALL';
}
