<?php
namespace Aneris\Module\Smarty;

use Aneris\Container\ServiceLocatorInterface;
use Aneris\Mvc\ViewManagerInterface;
use Aneris\Moudle\Smarty\Exception;

class SmartyView implements ViewManagerInterface
{
    private $smarty;
    private $postfix;

    private function getSmarty($serviceManager)
    {
        if($this->smarty)
            return $this->smarty;
        $this->smarty = $serviceManager->get('Smarty');
        if(isset($config['smarty']['postfix']))
            $this->postfix = $config['smarty']['postfix'];
        else
            $this->postfix = '.tpl.html';
        return $this->smarty;
    }

    public function render($response,$templateName,$templatePaths,$context)
    {
        $smarty = $this->getSmarty($context->getServiceLocator());

        $smarty->template_dir = $templatePaths;

        $response['pathprefix'] = $context->getRequest()->getPathPrefix();
        $smarty->clearAllAssign();
        foreach($response as $name => $value) {
            $smarty->assign($name,$value);
        }
        return $smarty->fetch($templateName.$this->postfix);
    }
}
