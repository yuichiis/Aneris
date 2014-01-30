<?php
if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
    if (!getenv('APPDATA')) {
        throw new \RuntimeException('The APPDATA or COMPOSER_HOME environment variable must be set for composer to run correctly');
    }
    $globalHome = strtr(getenv('APPDATA'), '\\', '/') . '/Composer';
} else {
    if (!getenv('HOME')) {
        throw new \RuntimeException('The HOME or COMPOSER_HOME environment variable must be set for composer to run correctly');
    }
    $globalHome = rtrim(getenv('HOME'), '/') . '/.composer';
}
if (defined('ANERIS_PATH')) {
    $anerisPath = ANERIS_PATH;
} elseif (is_dir('vendor/ANERIS/library')) {
    $anerisPath = 'vendor/ANERIS/library';
} elseif (getenv('ANERIS_PATH')) {
    $anerisPath = getenv('ANERIS_PATH');
} elseif (get_cfg_var('aneris_path')) {
    $anerisPath = get_cfg_var('aneris_path');
} else {
    $anerisPath = require 'Aneris/Aneris/library/Aneris/Loader/GetDirectory.php';
}
if (file_exists($globalHome.'/vendor/autoload.php')) {
    $loader = include $globalHome.'/vendor/autoload.php';
}
if ($anerisPath) {
    if(isset($loader)) {
        $loader->add('Aneris', $anerisPath);
        //$loader->add('AnerisZFModule', $anerisPath.'/../..');
        $loader->addClassMap(array('Smarty'=>'/xampp/PHPLibrary/Smarty-3.1.15/libs/Smarty.class.php'));
    } else {
        include $anerisPath . '/Aneris/Loader/AutoLoader.php';
        Aneris\Loader\Autoloader::factory(array(
            'namespaces' => array(
                'Aneris' => $anerisPath . '/Aneris',
                'Acme' => __DIR__ . '/src/Acme',
            ),
        ));
    }
}
