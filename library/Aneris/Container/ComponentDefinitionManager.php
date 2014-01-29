<?php
namespace Aneris\Container;

use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Container\Annotations\Named;
use Aneris\Container\Exception;
use ArrayObject;

class ComponentDefinitionManager
{
    const NAMED_COMPONENT_ANNTATION = 'Aneris\Container\Annotations\Named';
    protected $aliases = array();
    protected $components = array();
    protected $enableCache = true;
    protected $namedComponentCache;
    protected $cachePath;

    public function setEnableCache($enableCache=true)
    {
        $this->enableCache = $enableCache;
    }

    public function setCachePath($cachePath)
    {
        $this->cachePath = $cachePath;
    }

    public function attachScanner(ComponentScanner $componentScanner)
    {
        $componentScanner->attachCollect(
            self::NAMED_COMPONENT_ANNTATION,
            array($this,'collectNamedComponent'));
        $componentScanner->attachCompleted(
            self::NAMED_COMPONENT_ANNTATION,
            array($this,'completedScan'));
    }

    public function getNamedComponentCache()
    {
        if($this->namedComponentCache)
            return $this->namedComponentCache;

        if(!$this->enableCache) {
            return $this->namedComponentCache = new ArrayObject();
        }
        if($this->cachePath)
            $path = $this->cachePath;
        else
            $path = '/'.__CLASS__;

        $this->namedComponentCache = CacheFactory::getInstance($path.'/named',true);
        return $this->namedComponentCache;
    }

    public function hasNamedComponent($alias)
    {
        return isset($this->namedComponentCache[$alias]);
    }

    public function getNamedComponent($alias)
    {
        if(!isset($this->namedComponentCache[$alias]))
            return false;
        return $this->namedComponentCache[$alias];
    }

    public function getComponent($componentName,$force=false)
    {
        if(!isset($this->components[$componentName])) {
            if(!$force)
                return false;
            $this->components[$componentName] = $this->newComponent($componentName);
        }
        return $this->components[$componentName];
    }
    
    public function newComponent($componentName)
    {
        return new ComponentDefinition(array('name'=>$componentName,'class'=>$componentName));
    }

    public function hasComponent($componentName)
    {
        return isset($this->components[$componentName]);
    }

    public function setConfig($config)
    {
        if(array_key_exists('cache_path',$config)) {
            $this->setCachePath($config['cache_path']);
        }
        if(array_key_exists('enable_cache',$config)) {
            $this->setEnableCache($config['enable_cache']);
        }
        if(isset($config['aliases']))
            $this->aliases = $config['aliases'];

        if(isset($config['components'])) {
            if(!is_array($config['components']))
                throw new Exception\DomainException('components field must be array.');
            foreach($config['components'] as $name => $componentConfig) {
                $componentConfig['name'] = $name;
                if(!isset($componentConfig['class']) && !isset($componentConfig['factory']))
                    $componentConfig['class'] = $name;
                $this->components[$name] = new ComponentDefinition($componentConfig);
            }
        }
        $this->getNamedComponentCache();
    }

    public function resolveAlias($alias)
    {
        if(isset($this->aliases[$alias])) {
            $alias = $this->aliases[$alias];
        }
        $componentNamed = $this->getNamedComponent($alias);
        if($componentNamed)
            return $componentNamed;
        return $alias;
    }

    public function addAlias($alias, $componentName)
    {
        if(isset($this->aliases[$alias]))
            throw new Exception\DomainException('Already defined the alias:'.$alias);
        $this->aliases[$alias] = $componentName;
    }

    public function hasScanned()
    {
        return isset($this->namedComponentCache['__INITIALIZED__']);
    }

    public function completedScan()
    {
        $this->namedComponentCache['__INITIALIZED__'] = true;
    }

    public function collectNamedComponent($annoName,$className,$anno,$classRef)
    {
        if($anno instanceof Named) {
            if($anno->value==null)
                throw new Exception\DomainException('alias name is not specified.: '.$classRef->getFilename().'('.$classRef->getStartLine().')');
            if(isset($this->namedComponentCache[$anno->value]))
                throw new Exception\DomainException('duplicate alias name.: '.$classRef->getFilename().'('.$classRef->getStartLine().')');
            $this->namedComponentCache[$anno->value] = $className;
            return true;
        }
    }
}
