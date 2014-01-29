<?php
namespace Aneris\Mvc\Plugin;

use Aneris\Mvc\Exception;

class Di
{
    protected $pluginManager;

    public static function factory($pluginManager)
    {
        return new self($pluginManager);
    }
    public function __construct($pluginManager)
    {
        $this->pluginManager = $pluginManager;
        return $this;
    }

    public function __invoke($name)
    {
        $serviceLocator = $this->pluginManager->getParentManager();
        if($serviceLocator==null)
            throw new Exception\DomainException('parent service locator is null.');
        return $serviceLocator->get($name);
    }
}
