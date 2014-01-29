<?php
namespace Aneris\Module\Twig;

use Aneris\Mvc\ViewManagerInterface;

class TwigView implements ViewManagerInterface
{
    private function getTwig($context)
    {
        $pluginManager = $context->getPluginManager();
        return TwigFactory::factory($pluginManager);
    }

    private function getPostfix($context)
    {
        $config = $context->getServiceLocator()->get('config');
        if(isset($config['twig']['postfix']))
            return $config['twig']['postfix'];
        else
            return '.twig.html';
    }

    public function render($response,$templateName,$templatePaths,$context)
    {
        $twig = $this->getTwig($context);
        $loader = $twig->getLoader();
        $loader->setPaths($templatePaths);

        return $twig->render($templateName.$this->getPostfix($context), $response);
    }
}
