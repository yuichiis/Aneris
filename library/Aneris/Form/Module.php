<?php
namespace Aneris\Form;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'aliases' => array(
                    'Aneris\Form\View\FormRendererService' => 'Aneris\Form\View\FormRenderer',
                    'Aneris\Form\FormContextBuilderService' => 'Aneris\Form\FormContextBuilder',
                ),
                'components' => array(
                    'Aneris\Form\FormContextBuilder' => array(
                        'constructor_args' => array(
                            'validator' => array('ref' => 'Aneris\Validator\ValidatorService'),
                        ),
                    ),
                    'Aneris\Form\View\FormRenderer'  => array(
                        'constructor_args' => array(
                            'themes' => array('ref' => 'Aneris\Form\Config\Themes'),
                            'translator' => array('ref' => 'Aneris\Stdlib\I18n\TranslatorService'),
                            'textDomain' => array('ref' => 'Aneris\Form\Config\TranslatorTextDomain'),
                        ),
                    ),
                    'Aneris\Form\Config\Themes'  => array(
                        'factory' => 'Aneris\Container\ConfigurationFactory::factory',
                        'factory_args' => array('config'=>'form::themes'),
                        'proxy' => 'disable',
                    ),
                    'Aneris\Form\Config\TranslatorTextDomain'  => array(
                        'factory' => 'Aneris\Container\ConfigurationFactory::factory',
                        'factory_args' => array('config'=>'form::translator_text_domain'),
                        'proxy' => 'disable',
                    ),
                ),
            ),
            'mvc' => array(
                'plugins' => array(
                    'form' => 'Aneris\Form\Plugin\Form',
                ),
            ),
        );
    }
}
