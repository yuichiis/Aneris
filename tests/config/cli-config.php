<?php
require __DIR__.'/../init_autoloader.php';
$moduleManager = new Aneris\Container\ModuleManager(require __DIR__.'/doctrine.config.php');
$entityManager = $moduleManager->init()->getServiceLocator()->get('Doctrine\ORM\EntityManager');
return Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
