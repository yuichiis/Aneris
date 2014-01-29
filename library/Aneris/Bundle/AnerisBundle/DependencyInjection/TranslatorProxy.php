<?php
namespace Aneris\Bundle\AnerisBundle\DependencyInjection;

use Symfony\Component\Translation\TranslatorInterface;

class TranslatorProxy
{
	public function __construct(TranslatorInterface $translator) {
		$this->translator = $translator;
	}

	public function translate($message, $domain=null, $locale=null)
	{
		return $this->translator->trans($message,array(),$domain,$locale);
	}

	public function setLocale($locale)
	{
		return $this->translator->setLocale($locale);
	}

	public function getLocale()
	{
		return $this->translator->getLocale();
	}
}