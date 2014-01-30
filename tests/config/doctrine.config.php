<?php
return array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Module\Doctrine\Module' => true,
                ),
            ),
            'doctrine' => array(
                'paths' => array(__DIR__.'/../resources/AcmeTest/Doctrine1/Entity'),
                'is_devmode' => false,
                'connection' => array(
                    'driver' => 'pdo_sqlite',
                    'path' => __DIR__ . '/../data/db.sqlite',
                ),
            ),
);
