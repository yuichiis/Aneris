<?php
namespace Aneris\Loader;

class AutoLoader
{
    protected static $_instance=null;
    protected $namespaces = array();

    public static function getInstance()
    {
        if(self::$_instance === null)
            self::$_instance = new self();
        return self::$_instance;
    }

    public static function factory(array $config = array())
    {
        if(self::$_instance)
            return self::$_instance;

        $autoloader = self::getInstance();
        if(isset($config['namespaces']))
            $autoloader->setNameSpaces($config['namespaces']);

        spl_autoload_register(array($autoloader, 'autoload'));
        return $autoloader;
    }

    public function autoload($className)
    {
        list($path,$subClassName) = $this->mapping($className);
        $subClassName = str_replace('\\','/',$subClassName);
        $filename = $path.'/'.$subClassName.'.php';
        if(file_exists($filename)) {
            include_once $filename;
        }
    }

    private function setNameSpaces(array $namespaces)
    {
        $this->namespaces = $namespaces;
    }

     public function setNameSpace($namespace,$path)
    {
        $this->namespaces[$namespace] = $path;
    }

    private function mapping($className)
    {
        $pos = strpos($className,'\\');
        if($pos===false) {
            return null;
        }
        $namePrefix = substr($className,0,$pos);
        if(empty($namePrefix)) {
            return null;
        }
        $subClassName = substr($className,$pos+1);
        if(empty($subClassName)) {
            return null;
        }
        if(!isset($this->namespaces[$namePrefix])) {
            return null;
        }
        $path = $this->namespaces[$namePrefix];
        return array($path,$subClassName);
    }
}
