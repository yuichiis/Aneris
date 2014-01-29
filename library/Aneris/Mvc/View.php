<?php
namespace Aneris\Mvc;

class View
{
    public $headers;
	public $content;
	public $layout;
	protected $pluginManager;

	public function __construct(PluginManager $pluginManager)
	{
		$this->pluginManager = $pluginManager;
	}

    public function renderTemplate($templateFullPath,$templateVariables)
    {
        if(is_array($templateVariables))
            extract($templateVariables,EXTR_PREFIX_SAME,'var_');
        try {
            ob_start();
            require $templateFullPath;
            return ob_get_clean();
        }
        catch(\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

    public function __call($method,$params)
    {
        if($this->pluginManager==null)
            throw new Exception\DomainException('pluginManager is not specified.');
        $plugin = $this->pluginManager->get($method);
        if (is_callable($plugin)) {
            return call_user_func_array($plugin, $params);
        }

        return $plugin;
    }
}