<?php
namespace Aneris\Module\Smarty;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'aliases' => array(
                    'SmartyService' => 'Smarty',
                ),
                'components' => array(
                    'Smarty' => array(
                        'class' => 'Smarty',
                        'factory' => 'Aneris\Module\Smarty\SmartyFactory::factory',
                    ),
                ),
            ),
        );
    }
}
