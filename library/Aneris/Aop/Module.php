<?php
namespace Aneris\Aop;

class Module
{
    public function getConfig()
    {
        return array(
            'module_manager' => array(
                'aop_manager' => 'Aneris\Aop\AopManager',
            ),
        );
    }
}
