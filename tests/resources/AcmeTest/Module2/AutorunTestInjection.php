<?php
namespace AcmeTest\Module2;

class AutorunTestInjection
{
	protected $response = __CLASS__;
	public function invoke()
	{
		return $this->response;
	}

	public function setConfig($config)
	{
		$this->response = $config['testRunConfigInjection']['response'];
	}
}