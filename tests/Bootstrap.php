<?php
date_default_timezone_set('UTC');

include 'init_autoloader.php';
$loader->add('AcmeTest', __DIR__ . '/resources');
$loader->add('SfTest',   __DIR__ . '/AnerisBundle/src');
define('ANERIS_TEST_RESOURCES',__DIR__.'/resources');
define('ANERIS_TEST_DATA',     __DIR__.'/data');
define('ANERIS_TEST_CONFIG',   __DIR__.'/config');
define('ANERIS_REPOSITORY_ROOT',   __DIR__.'/..');

global $_SERVER;
$_SERVER["KERNEL_DIR"] = __DIR__.'/AnerisBundle/app/';

Aneris\Stdlib\Cache\CacheFactory::$fileCachePath = __DIR__.'/cache';
Aneris\Stdlib\Cache\CacheFactory::$enableApcCache = false;
Aneris\Stdlib\Cache\CacheFactory::$enableFileCache = true;
Aneris\Stdlib\Cache\CacheFactory::clearCache();

