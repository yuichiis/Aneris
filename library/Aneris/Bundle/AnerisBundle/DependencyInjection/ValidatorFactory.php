<?php
namespace Aneris\Bundle\AnerisBundle\DependencyInjection;

use Aneris\Validator\Validator;
use Aneris\Bundle\AnerisBundle\Exception;

class ValidatorFactory
{
	public static function factory($translator)
	{
		if($translator==null)
			throw new Exception\DomainException('translator is not specified.');
		$translator->bindTextDomain(
			Validator::getTranslatorTextDomain(),
			Validator::getTranslatorBasePath()
		);
		return new Validator($translator);
	}
}
