<?php
namespace Aneris\Form\View;

use Aneris\Container\ServiceLocatorInterface;

class FormRendererFactory
{
    const DEFAULT_TRANSLATOR_SERVICE = 'Aneris\Stdlib\I18n\Gettext';

    public static function newInstance(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $config = $config['form'];

        $translator = null;
        $textDomain = null;
        $themes = null;

        if(isset($config['translator']))
            $translatorName = $config['translator'];
        else
            $translatorName = self::DEFAULT_TRANSLATOR_SERVICE;

        if($serviceLocator->has($translatorName))
            $translator = $serviceLocator->get($translatorName);

        if(isset($config['translator_text_domain']))
            $textDomain = $config['translator_text_domain'];

        if(isset($config['themes']))
            $themes = $config['themes'];

        $form = new FormRenderer($themes,$translator,$textDomain);

        return $form;
    }
}