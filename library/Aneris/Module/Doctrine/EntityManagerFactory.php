<?php
namespace Aneris\Module\Doctrine;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Aneris\Container\ServiceLocatorInterface;
use Aneris\Moudle\Doctrine\Exception;

class EntityManagerFactory
{
    public static function factory(ServiceLocatorInterface $serviceManager)
    {
        $config = $serviceManager->get('config');
        if(!isset($config['doctrine']))
            throw new Exception\DomainException("Configuration is not found for Doctrine");
        $config = $config['doctrine'];
        if(!isset($config['paths']))
            throw new Exception\DomainException("Configuration paths are not found in Doctrine Configuration");
        if(!isset($config['connection']))
            throw new Exception\DomainException("Database connection informations are not found in Doctrine Configuration");

        if(isset($config['is_devmode']))
            $isDevMode = $config['is_devmode'];
        else
            $isDevMode = false;

        if(isset($config['annotation_reader'])) {
            $annotationReader = new $config['annotation_reader'];
            $setup = self::createAnnotationMetadataConfiguration($config['paths'], $isDevMode,null,null,$annotationReader);
        } else {
            $useSimpleAnnotationReader = false;
            $setup = Setup::createAnnotationMetadataConfiguration($config['paths'], $isDevMode,null,null,$useSimpleAnnotationReader);
        }
        return EntityManager::create($config['connection'], $setup);
    }

    private static function createAnnotationMetadataConfiguration($paths=array(),$isDevMode=false, $proxyDir=null, $cache=null,$annotationReader=null)
    {
        if($annotationReader==null)
            $annotationReader = new AnnotationReaderProxy();
        $annotationDriver = new AnnotationDriver(
            new CachedReader($annotationReader, new ArrayCache()),
            (array) $paths
        );
        $config = Setup::createConfiguration($isDevMode, $proxyDir, $cache);
        $config->setMetadataDriverImpl($annotationDriver);
        return $config;
    }
}