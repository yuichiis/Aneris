<?php
namespace Aneris\Container;

use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Container\Exception;
use ArrayObject;

class DefinitionManager
{
    protected $runtimeComplie = true;
    protected $cachePath;
    protected $definitionCache;
    protected $namedComponentCache;
    protected $enableCache = true;
    protected $annotationManager;

    public function setEnableCache($enableCache=true)
    {
        $this->enableCache = $enableCache;
    }

    public function setCachePath($cachePath)
    {
        $this->cachePath = $cachePath;
    }

    public function getDefinitionCache()
    {
        if($this->definitionCache)
            return $this->definitionCache;

        if(!$this->enableCache) {
            return $this->definitionCache = new ArrayObject();
        }

        if($this->cachePath)
            $path = $this->cachePath;
        else
            $path = '/'.__CLASS__;

        $this->definitionCache = CacheFactory::getInstance($path.'/definition');
        return $this->definitionCache;
    }

    public function setRuntimeComplie($complie = true)
    {
        $this->runtimeComplie = $complie;
    }

    public function getDefinition($className)
    {
        $definitionCache = $this->getDefinitionCache();
        if(isset($definitionCache[$className]))
            return $definitionCache[$className];

        if(!$this->runtimeComplie)
            throw new Exception\DomainException($className.' does not defined',0);

        $definition = $this->complieClassDefinition($className);

        $definitionCache[$className] = $definition;
        return $definition;
    }

    public function setDefinition($className, Definition $definition)
    {
        $definitionCache = $this->getDefinitionCache();
        $definitionCache[$className] = $definition;
    }

    public function complieClassDefinition($className)
    {
        return new Definition($className,$this->annotationManager);
    }

    public function setAnnotationManager($annotationManager)
    {
        $this->annotationManager = $annotationManager;
    }
    
    public function setConfig($config)
    {
        if(array_key_exists('runtime_complie',$config)) {
            $this->setRuntimeComplie($config['runtime_complie']);
        }
        if(array_key_exists('cache_path',$config)) {
            $this->setCachePath($config['cache_path']);
        }
        if(array_key_exists('enable_cache',$config)) {
            $this->setEnableCache($config['enable_cache']);
        }
        if(isset($config['definitions'])) {
            $definitionCache = $this->getDefinitionCache();
            foreach($config['definitions'] as $className => $defConfig) {
                $definition = $this->complieClassDefinition($defConfig);
                $definitionCache[$definition->getClassName()] = $definition;
            }
        }
    }
}
