<?php
return array(
'aneris' => array(
    'module_manager' => array(
        'modules' => array(
            'ZFTest\Model\Module' => true,
            'Aneris\Mvc\Module' => true,
            'Aneris\Module\Twig\Module' => true,
            'Aneris\Module\Smarty\Module' => true,
            'Aneris\Module\Doctrine\Module' => true,
            'Aneris\Form\Module' => true,
            'Aneris\Validator\Module' => true,
            'Aneris\Stdlib\I18n\Module' => true,
        ),
        'autorun' => 'Aneris\Mvc\Module',
    ),
    'cache' => array(
        'enableApcCache'  => true,
        'enableFileCache' => true,
        'forceFileCache'  => false,
        'fileCachePath'   => __DIR__.'/../../cache',
        'apcTimeOut'      => 20,
    ),
    //'service_manager' => array(
    //	'aliases' => array(
    //		'MvcViewManager' => 'Aneris\Mvc\Module\Twig\TwigView',
    //	),
    //),
/*
    'twig' => array(
        'cache'    => __DIR__.'/../cache/twig',
    ),
    'smarty' => array(
        'cache_dir'   => __DIR__.'/../cache/smarty/cache',
        'compile_dir' => __DIR__.'/../cache/smarty/templates_c',
    ),
    'mvc' => array(
        'dispatcher' => array(
            'use_di' => true,
        ),
        'view' => array(
            'layout' => 'layout/layout',
            //'layout' => 'layout/bootstrap',
            'service' => array(
                'default' => 'Aneris\Mvc\ViewManager',
            ),
            'error_policy' => array(
                'display_detail' => true,
                //'redirect_url' => '/',
                //'not_found_page' => '404',
                //'exception_page' => '503',
            ),
        ),
    ),
*/
    'container' => array(
        'annotation_manager' => true,
        'aliases' => array(
            'EntityManager' => 'Doctrine\ORM\EntityManager',
        ),
    ),
    'doctrine' => array(
        'paths' => array(__DIR__.'/../src/Acme/MyApp/Entity'),
        'is_devmode' => true,
        'annotation_reader' => 'Aneris\Module\Doctrine\AnnotationReaderProxy',
        'connection' => array(
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/../data/db.sqlite',
        ),
    ),
    'form' => array(
        'themes' => array(
            'default'    => 'Aneris\Form\View\Theme\Foundation5Horizontal',
            'bootstrap'  => 'Aneris\Form\View\Theme\Bootstrap3Horizontal',
            'foundation' => 'Aneris\Form\View\Theme\Foundation5Horizontal',
        ),
    ),
));
