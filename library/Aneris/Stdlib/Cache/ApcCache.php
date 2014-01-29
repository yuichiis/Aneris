<?php
namespace Aneris\Stdlib\Cache;

use ArrayAccess;

class ApcCache implements ArrayAccess
{
    protected $cachePath;
    protected $timeOut = 360; // 360 seconds = 10 minute.

    public function __construct($cachePath=null, $timeOut=null)
    {
    	if(!extension_loaded('apc'))
    		throw new Exception\DomainException('apc extension is not loaded.');
        if($cachePath!==null)
	        $this->setCachePath($cachePath);
        if($timeOut!==null)
	        $this->setTimeOut($timeOut);
    }
    
    public function setCachePath($cachePath)
    {
        $this->cachePath = $cachePath;
        return $this;
    }
    
    public function getCachePath()
    {
        return $this->cachePath;
    }

    public function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;
        return $this;
    }

    public function offsetExists($offset)
    {
        $key = $this->cachePath . '/' . $offset;
        return apc_exists($key);
    }

    public function offsetGet($offset)
    {
        $key = $this->cachePath . '/' . $offset;
        return apc_fetch($key);
    }

    public function offsetSet($offset,$value)
    {
        $key = $this->cachePath . '/' . $offset;
        apc_store($key, $value, $this->timeOut);
        return $this;
    }

    public function offsetUnset($offset)
    {
        $key = $this->cachePath . '/' . $offset;
		apc_delete($key);
        return $this;
    }

    public function cacheExists($offset)
    {
        return $this->offsetExists($offset);
    }

    public function loadCache($offset)
    {
        return $this->offsetGet($offset);
    }

    public function saveCache($offset, $value)
    {
        $this->offsetSet($offset,$value);
        return $this;
    }

    public function deleteCache($offset)
    {
        $this->offsetUnset($offset);
        return $this;
    }

    public function hasFileStorage()
    {
        return false;
    }
}