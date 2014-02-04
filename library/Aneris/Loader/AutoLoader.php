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
        spl_autoload_register(array(self::$_instance, 'autoload'));
        return self::$_instance;
    }

    public static function factory(array $config = array())
    {
        $autoloader = self::getInstance();
        if(isset($config['namespaces']))
            $autoloader->addNameSpaces($config['namespaces']);
        return $autoloader;
    }

    public function autoload($className)
    {
        list($paths,$filename) = $this->mapping($className);
        if($paths) {
            if($this->includeFile($paths,$filename))
                return;
        }
        if(isset($this->namespaces['__ROOT__'])) {
            if($this->includeFile($this->namespaces['__ROOT__'],$filename))
                return;
        }
    }

    private function includeFile($paths,$filename)
    {
        foreach ($paths as $path) {
            $fullfilename = $path.'/'.$filename;
            if(file_exists($fullfilename)) {
                include_once $fullfilename;
                return true;
            }
        }
        return false;
    }

    public function addNameSpaces(array $namespaces)
    {
        foreach($namespaces as $namespace => $path) {
            $this->namespaces[$namespace][] = $path;
        }
    }

    public function add($namespace,$path)
    {
        if(empty($namespace))
            $namespace = '__ROOT__';
        $this->namespaces[$namespace][] = $path;
    }

    private function mapping($className)
    {
        if(false !== $pos = strpos($className,'\\')) {
            $separator = '\\';
        } else if(false !== $pos = strpos($className,'_')){
            $separator = '_';
        }
        $paths = null;
        if($pos!==false) {
            $namePrefix = substr($className,0,$pos);
            if(!empty($namePrefix)) {
                if(isset($this->namespaces[$namePrefix])) {
                    $paths = $this->namespaces[$namePrefix];
                }
            }
            $filename = str_replace($separator, '/', $className).'.php';
        } else {
            $filename = $className.'.php';
        }
        return array($paths,$filename);
    }
}
