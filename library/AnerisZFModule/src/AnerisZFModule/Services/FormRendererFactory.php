<?php
namespace AnerisZFModule\Services;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Aneris\Form\View\FormRenderer;

class FormRendererFactory implements FactoryInterface
{
    const DEFAULT_TRANSLATOR_SERVICE = 'MvcTranslator';

    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $config = $serviceManager->get('Config');
        if(isset($config['form']))
            $config = $config['form'];
        else
            $config = array();
        $translator = null;
        $textDomain = null;
        $themes = null;

        if(isset($config['translator']))
            $translatorName = $config['translator'];
        else
            $translatorName = self::DEFAULT_TRANSLATOR_SERVICE;

        if($serviceManager->has($translatorName))
            $translator = $serviceManager->get($translatorName);

        if(isset($config['translator_text_domain']))
            $textDomain = $config['translator_text_domain'];

        if(isset($config['themes']))
            $themes = $config['themes'];

        $form = new FormRenderer($themes,$translator,$textDomain);
        return $form;
    }
}