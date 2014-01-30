<?php
return  array(
    'module_setting' => array(
        'each_setting' => array(
            $namespace => 'module2',
        ),
        'share_setting' => array(
            'paths' => array(
                $namespace => 'testpath2',
            ),
        ),
    ),
/*
    'module_manager' => array(
        'invokables' => array(
            $namespace => array(
                'container' => 'Di',
                'class' => 'AcmeTest\Module2\AutorunTest',
            ),
        ),
    ),
*/
    'di' => array(
    	'definition_manager' => array(
    	),
   	),
);
