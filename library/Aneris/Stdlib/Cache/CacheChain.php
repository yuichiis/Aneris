<?php
namespace Aneris\Stdlib\Cache;

use ArrayAccess;
use ArrayObject;

class CacheChain implements ArrayAccess
{
	protected $cache;
	protected $storage;

	public function __construct(ArrayAccess $storage=null,ArrayAccess $cache=null)
	{
		$this->storage = $storage;
		if($cache==null) 
			$this->cache = new ArrayObject();
        else
            $this->cache = $cache;
	}

    public function getStorage()
    {
        return $this->storage;
    }

    public function getCache()
    {
        return $this->cache;
    }

    public function offsetExists($offset)
    {
        if($this->cache->offsetExists($offset))
        	return true;
        if($this->storage)
        	return $this->storage->offsetExists($offset);
        return false;
    }

    public function offsetGet($offset)
    {
        if($this->cache->offsetExists($offset))
        	return $this->cache->offsetGet($offset);
        if($this->storage) {
            if($this->storage->offsetExists($offset)) {
                $value = $this->storage->offsetGet($offset);
                $this->cache->offsetSet($offset,$value);
                return $value;
            }
        }
        //throw new Exception\OutOfRangeException('Offset invalid or out of range.'); 
        trigger_error('Offset invalid or out of range.',E_USER_NOTICE);
        return null;
    }

    public function offsetSet($offset, $value)
    {
        $this->cache->offsetSet($offset, $value);
        if($this->storage)
        	$this->storage->offsetSet($offset, $value);
        return $this;
    }

    public function offsetUnset($offset)
    {
        if($this->cache->offsetExists($offset))
            $this->cache->offsetUnset($offset);
        if($this->storage)
            if($this->storage->offsetExists($offset))
	           $this->storage->offsetUnset($offset);
        return $this;
    }

    public function cacheExists($offset)
    {
        return $this->offsetExists($offset);
    }

    public function loadCache($offset)
    {
    	return $this->offsetSet($offset);
    }

    public function saveCache($offset,$value)
    {
    	$this->offsetSet($offset, $value);
        return $this;
    }

    public function deleteCache($offset)
    {
        $this->offsetUnset($offset);
        return $this;
    }

    public function hasFileStorage()
    {
        if(method_exists($this->storage, 'hasFileStorage') && $this->storage->hasFileStorage())
            return true;
        if(method_exists($this->cache, 'hasFileStorage') && $this->cache->hasFileStorage())
            return true;
        return false;
    }
}