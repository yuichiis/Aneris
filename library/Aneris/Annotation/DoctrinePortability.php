<?php
/**
 * Compatibility For Doctrine Annotation Reader
 */
namespace Aneris\Annotation;

class DoctrinePortability
{
	public static function patch()
	{
		if(!defined('TYPE'))
			define('TYPE','CLASS');
		if(!defined('FIELD'))
			define('FIELD','PROPERTY');
		if(!defined('METHOD'))
			define('METHOD','METHOD');
		if(!defined('ANNOTATION_TYPE'))
			define('ANNOTATION_TYPE','CLASS');
		if(!defined('PARAMETER'))
			define('PARAMETER','ANNOTATION');
		#if(!defined('CONSTRUCTOR'))
		#	define('CONSTRUCTOR','METHOD');
	}
}
