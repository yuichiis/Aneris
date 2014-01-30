<?php
namespace AnerisTest\DoctrineTest;

use Aneris\Container\ModuleManager;
use Aneris\Stdlib\Cache\CacheFactory;

// Test Target Classes
//use Doctrine\ORM\EntityManager; // on ModuleManager

class DoctrineTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/doctrine/cache');
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/doctrine/proxyDir');
    }

    public function setUp()
    {
        $this->doctrineConfig = require ANERIS_TEST_CONFIG . '/doctrine.config.php';
    }

    public function testPure()
    {
        $paths = array(
            ANERIS_TEST_RESOURCES.'/AcmeTest/Doctrine1/Entity',
        );
        $isDevMode = false;
        $connection = array(
            'driver' => 'pdo_sqlite',
            'path' => ANERIS_TEST_DATA . '/db.sqlite',
        );
        $proxyDir = CacheFactory::$fileCachePath.'/doctrine/proxyDir';
        $cache = new \Doctrine\Common\Cache\FilesystemCache(CacheFactory::$fileCachePath.'/cache/doctrine');
        //$annotationCache = new Doctrine\Common\Cache\ApcCache();
        $useSimpleAnnotationReader = false;

        $setup = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);

        //$setup = Doctrine\ORM\Tools\Setup::createConfiguration($isDevMode, $proxyDir, $cache);
        //$setup->newDefaultAnnotationDriver($paths, $useSimpleAnnotationReader);
        //$reader = new Doctrine\Common\Annotations\SimpleAnnotationReader();
        //$reader->addNamespace('Doctrine\ORM\Mapping');
        //$cachedReader = new Doctrine\Common\Annotations\CachedReader($reader, $annotationCache);
        //$cachedReader = new Doctrine\Common\Annotations\FileCacheReader($reader, CacheFactory::$fileCachePath.'/doctrine/cache');
        //$annotationDriver = new Doctrine\ORM\Mapping\Driver\AnnotationDriver($cachedReader, (array) $paths);
        //$setup->setMetadataDriverImpl($annotationDriver);

        //$product = new AcmeTest\Doctrine1\Entity\Product();
        $entityManager = \Doctrine\ORM\EntityManager::create($connection, $setup);
        //$entityManager->persist($product);
        //$entityManager->flush();
        $product2 = $entityManager->find('AcmeTest\Doctrine1\Entity\Product',1);
        print_r($product2);
        $e = new \Doctrine\ORM\Mapping\Entity();
    }

     public function testDefault()
    {
        $moduleManager = new ModuleManager($this->doctrineConfig);
        $entityManager = $moduleManager->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $this->assertEquals('Doctrine\ORM\EntityManager',get_class($entityManager));

        $newProductName = 'apple';

        //$product = new AcmeTest\Doctrine1\Entity\Product();
        //$product->setName($newProductName);

        //$entityManager->persist($product);
        //$entityManager->flush();

        //$id = $product->getId();

        $product2 = $entityManager->find('AcmeTest\Doctrine1\Entity\Product',1);
        //echo get_class($product2);
        //$this->assertEquals($id,$product2->getId());
        //$oid  = spl_object_hash($product);
        //$oid2 = spl_object_hash($product2);
        //$this->assertEquals($oid,$oid2);
    }
/*
    public function testCache()
    {
        $reader = new Doctrine\Common\Annotations\FileCacheReader(
            new Doctrine\Common\Annotations\AnnotationReader(),
            CacheFactory::$fileCachePath.'/doctrine/annotation/cache',
            $debug = false,
        );

        AnnotationRegistry::registerLoader(function($class) {
            $file = str_replace("\\", DIRECTORY_SEPARATOR, $class) . ".php";

            if (file_exists("/my/base/path/" . $file)) {
                // file exists makes sure that the loader fails silently
                require "/my/base/path/" . $file;
            }
        });
*/
}
