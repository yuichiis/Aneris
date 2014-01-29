<?php
namespace Aneris\Module\Twig;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'aliases' => array(
                    'Twig_EnvironmentService' => 'Twig_Environment',
                ),
                'components' => array(
                    'Twig_Environment' => array(
                        'class' => 'Twig_Environment',
                        'factory' => 'Aneris\Module\Twig\TwigFactory::factory',
                    ),
                ),
            ),
            'twig' => array(
                'extensions' => array(
                    'Form' => 'Aneris\Module\Twig\Extension\Form',
                    'Url' => 'Aneris\Module\Twig\Extension\Url',
                ),
            ),
        );
    }
}
