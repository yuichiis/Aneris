<?php
namespace Aneris\Stdlib\I18n;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'aliases' => array(
                    'Aneris\Stdlib\I18n\GettextService' => 'Aneris\Stdlib\I18n\Gettext',
                ),
                'components' => array(
                    'Aneris\Stdlib\I18n\Gettext' => array(
                        'class' => 'Aneris\Stdlib\I18n\Gettext',
                        'factory' =>  'Aneris\Stdlib\I18n\GettextFactory::factory',
                    ),
                ),
            ),
        );
    }
}
