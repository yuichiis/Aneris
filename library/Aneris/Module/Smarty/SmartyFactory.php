<?php
namespace Aneris\Module\Smarty;

use Aneris\Container\ServiceLocatorInterface;
use Aneris\Mvc\ViewManagerInterface;
use Aneris\Moudle\Smarty\Exception;

class SmartyFactory
{
    public static function factory(ServiceLocatorInterface $serviceManager)
    {
        $config = $serviceManager->get('config');
        $smarty_config = $config['smarty'];
        $smarty = new \Smarty();
        foreach ($smarty_config as $key => $value) {
            if($key == 'postfix')
                continue;
            $smarty->$key = $value;
        }

        return $smarty;
    }
}
