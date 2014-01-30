<?php
return array(
    'modules' => array(
        'ZFTest',
        'AnerisZFModule',
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            __DIR__.'/../module',
            __DIR__.'/../vendor',
        ),
        'config_glob_paths' => array(
            __DIR__.'/../config/autoload/{,*.}{global,local}.php',
        ),
    ),
);