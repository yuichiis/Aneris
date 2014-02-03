<?php
namespace Aneris\Stdlib\I18n;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'aliases' => array(
                    'Aneris\Stdlib\I18n\TranslatorService' => 'Aneris\Stdlib\I18n\Translator',
                ),
                'components' => array(
                    'Aneris\Stdlib\I18n\Translator' => array(
                        'class' => 'Aneris\Stdlib\I18n\Translator',
                        'factory' =>  'Aneris\Stdlib\I18n\TranslatorFactory::factory',
                    ),
                ),
            ),
        );
    }
}
