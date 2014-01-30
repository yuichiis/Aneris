<?php
require __DIR__.'/../../development/init_autoloader.php';
$moduleManager = new Aneris\Module\ModuleManager(require 'doctrine.config.php');
$entityManager = $moduleManager->init()->getServiceLocator()->get('Doctrine\ORM\EntityManager');
return Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
