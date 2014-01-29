<?php
namespace Aneris\Stdlib\Cache;

use ArrayAccess;

class FileCache implements ArrayAccess
{
    protected $cachePath;

    public function __construct($cachePath=null)
    {
        $this->setCachePath($cachePath);
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

    public function offsetExists($offset)
    {
        $filename = $this->cachePath . '/' . str_replace('\\', '/', $offset) . '.php';
        return file_exists($filename);
    }

    public function offsetGet($offset)
    {
        $filename = $this->cachePath . '/' . str_replace('\\', '/', $offset) . '.php';
        if(!file_exists($filename))
            return false;
        //return new Definition(require $filename);
        return require $filename;
    }

    public function offsetSet($offset,$value)
    {
        //if(!is_dir($this->cachePath))
        //    throw new Exception\DomainException('Cache directory is not exist:'.$this->cachePath);
        $code = "<?php\nreturn unserialize('".str_replace(array('\\','\''), array('\\\\','\\\''), serialize($value))."');";
        //$code = "<?php\nreturn unserialize(\"".str_replace(array("\\","\0","\"","\n","\r","\t"), array("\\\\","\\0","\\\"","\\n","\\r","\\t"), serialize($value))."\");";
        //$code = "<?php\nreturn unserialize(base64_decode('".base64_encode(serialize($value))."'));";
        $filename = $this->cachePath . '/' . str_replace('\\', '/', $offset) . '.php';
        if(!is_dir(dirname($filename))) {
            $dirname = dirname($filename);
            mkdir(dirname($filename),0777,true);
        }
        file_put_contents($filename, $code);
        return $this;
    }

    public function offsetUnset($offset)
    {
        $filename = $this->cachePath . '/' . str_replace('\\', '/', $offset) . '.php';
        if(!file_exists($filename))
            return false;
        unlink($filename);
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
        return true;
    }
}