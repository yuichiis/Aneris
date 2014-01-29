<?php
namespace Aneris\Container;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ReflectionClass;
use Aneris\Annotation\NameSpaceExtractor;

class ComponentScanner
{
    protected $annotationManager;
    protected $completedListener = array();
    protected $collectListener = array();

    public function setAnnotationManager($annotationManager)
    {
        $this->annotationManager = $annotationManager;
    }

    public function attachCollect($annotationName,$callback)
    {
        $this->collectListener[$annotationName][] = $callback;
    }

    public function attachCompleted($name,$callback)
    {
        $this->completedListener[$name][] = $callback;
    }

    public function scan(array $paths)
    {
        foreach ($paths as $path => $switch) {
            if($switch)
                $this->scanDirectory($path);
        }
        foreach ($this->completedListener as $name => $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func($callback,$name);
            }
        }
    }

    protected function scanDirectory($path)
    {
        if(!file_exists($path)) {
            throw new Exception\DomainException('component directory not found: '.$path);
        }
        $fileSPLObjects = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );
        foreach($fileSPLObjects as $fullFileName => $fileSPLObject) {
            $filename = $fileSPLObject->getFilename();
            if (!is_dir($fullFileName)) {
                if($filename!='.' && $filename!='..') {
                    $this->scanFile($fullFileName);
                }
            }
        }
        return $this;
    }

    protected function scanFile($filename)
    {
        require_once $filename;
        $parser = new NameSpaceExtractor($filename);
        $classes = $parser->getAllClass();
        if($classes==null)
            return $this;
        foreach($classes as $class) {
            $this->scanClass($class);
        }
        return $this;
    }

    protected function scanClass($class)
    {
        $isComponent = false;
        $classRef = new ReflectionClass($class);
        $annos = $this->annotationManager->getClassAnnotations($classRef);
        foreach ($annos as $anno) {
            foreach ($this->collectListener as $name => $callbacks) {
                if($name == get_class($anno)) {
                    foreach ($callbacks as $callback) {
                        call_user_func($callback,$name,$class,$anno,$classRef);
                    }
                }
            }
        }
    }
}