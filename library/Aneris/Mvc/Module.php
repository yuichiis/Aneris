<?php
namespace Aneris\Mvc;

class Module
{
    protected $mvcApp;

    public function getConfig()
    {
        return array(
            'module_manager' => array(
                'invokables' => array(
                    'Aneris\Mvc\Module' => array(
                        'class' => 'Aneris\Mvc\Application',
                        'method' => 'run',
                        //'config_injector' => 'setConfig',
                    ),
                ),
            ),
            'container' => array(
                'aliases' => array(
                    'Aneris\Mvc\ApplicationService' => 'Aneris\Mvc\Application',
                ),
                'components' => array(
                    'Aneris\Mvc\Application' => array(
                        'constructor_args' => array(
                            'serviceLocator' => array('ref' => 'ServiceLocator'),
                        ),
                        'properties' => array(
                            'config' => array('ref' => 'Aneris\Mvc\Config'),
                        ),
                    ),
                    'Aneris\Mvc\Config' => array(
                        'factory' => 'Aneris\Container\ConfigurationFactory::factory',
                        'factory_args' => array('config'=>'mvc'),
                        'proxy' => 'disable',
                    ),
                ),
            ),
            'mvc' => array(
                'plugins' => array(
                    'url'         => 'Aneris\Mvc\Plugin\Url',
                    'redirect'    => 'Aneris\Mvc\Plugin\Redirect',
                    'placeholder' => 'Aneris\Mvc\Plugin\Placeholder',
                    'di'          => 'Aneris\Mvc\Plugin\Di',
                ),
            ),
        );
    }
}