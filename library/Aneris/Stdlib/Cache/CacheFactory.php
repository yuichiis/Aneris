<?php
namespace Aneris\Stdlib\Cache;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class CacheFactory
{
    public static $enableApcCache  = true;
    public static $enableFileCache = true;
    public static $forceFileCache  = false;
    public static $fileCachePath;
    public static $apcTimeOut;
    public static $caches = array();

	public static function newInstance($path,$forceFileCache=null)
	{
    	if(extension_loaded('apc') && self::$enableApcCache) {
    		$apc = new ApcCache($path,self::$apcTimeOut);
    	}

    	if(self::$forceFileCache || $forceFileCache|| (!isset($apc) && self::$enableFileCache)) {
            $fileCachePath = (self::$fileCachePath) ? self::$fileCachePath : sys_get_temp_dir();
            $path = rtrim(str_replace('\\', '/', $fileCachePath)).'/'.trim(str_replace('\\', '/', $path),'/');
    		$file = new FileCache($path);
    	}

    	if(isset($file) && isset($apc)) {
    		$secondary = new CacheChain($file,$apc);
    		$primary = new CacheChain($secondary);
    	}
    	else if(isset($apc)) {
    		$primary = new CacheChain($apc);
    	}
    	else if(isset($file)) {
    		$primary = new CacheChain($file);
    	}
    	else {
    		$primary = new CacheChain();
    	}

    	return $primary;
	}

	public static function getInstance($path,$forceFileCache=null)
	{
		if(!isset(self::$caches[$path]))
			self::$caches[$path] = self::newInstance($path,$forceFileCache);

		return self::$caches[$path];
	}

    public static function clearCache()
    {
        self::clearFileCache();
        self::clearApcCache();
        self::$caches = array();
    }

    public static function clearFileCache($path=null)
    {
        if($path===null) {
            $fileCachePath = (self::$fileCachePath) ? self::$fileCachePath : sys_get_temp_dir();
            $path = rtrim(str_replace('\\', '/', $fileCachePath),'/');
        }
        if(!file_exists($path)) {
            return;
        }
        $fileSPLObjects = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path),
                RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach($fileSPLObjects as $fullFileName => $fileSPLObject) {
            $filename = $fileSPLObject->getFilename();
            if (is_dir($fullFileName)) {
                if($filename!='.' && $filename!='..') {
                    @rmdir($fullFileName);
                }
            } else {
                @unlink($fullFileName);
            }
        }
    }

    public static function clearApcCache($cacheType=null)
    {
        if(!extension_loaded('apc'))
            return;

        if($cacheType===null)
            $cacheType = 'user';
        apc_clear_cache($cacheType);
    }

    public static function setConfig($config=null)
    {
        if(!is_array($config))
            return;
        if(array_key_exists('enableApcCache', $config))
            self::$enableApcCache = $config['enableApcCache'];
        if(array_key_exists('enableFileCache', $config))
            self::$enableFileCache = $config['enableFileCache'];
        if(array_key_exists('forceFileCache', $config))
            self::$forceFileCache = $config['forceFileCache'];
        if(array_key_exists('fileCachePath', $config))
            self::$fileCachePath = $config['fileCachePath'];
        if(array_key_exists('apcTimeOut', $config))
            self::$apcTimeOut = $config['apcTimeOut'];
    }
}