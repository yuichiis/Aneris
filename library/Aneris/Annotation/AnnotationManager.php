<?php
namespace Aneris\Annotation;

use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;
use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Annotation\Annotations\Annotation as AnnotationTag;
use Aneris\Annotation\Annotations\Target as TargetTag;
use Aneris\Annotation\Annotations\Enum as EnumTag;
use Aneris\Annotation\Annotations\DontRegistAnnotationInterface;

class AnnotationManager
{
	private static $metaDataCache;
    private static $importsCache;
    private static $instance;
	private static  $cachePath;
	protected $parser;
    protected $nameSpaces;
    protected $ignoreUnknownAnnotationMode;
    protected $events;
    protected $annotationProvider = array();
    protected $dontRegistAnnotationInterface;

    public static function factory()
    {
        if(self::$instance)
            return self::$instance;
        return self::$instance = new self();
    }

	public function __construct()
	{
        $this->nameSpaces = array(__NAMESPACE__.'\\'.'Annotations'=>__NAMESPACE__.'\\'.'Annotations');
		$this->parser = new Parser($this);
        $this->dontRegistAnnotationInterface = __NAMESPACE__.'\DontRegistAnnotationInterface';
	}

    public function getParser()
    {
        return $this->parser;
    }

    public function addNameSpace($nameSpace)
    {
        $this->nameSpaces[$nameSpace] = $nameSpace;
        return $this;
    }

    public function addNameSpaces(array $nameSpaces)
    {
        foreach ($nameSpaces as $nameSpace) {
            $this->addNameSpace($nameSpace);
        }
        return $this;
    }

    public function ignoreUnknownAnnotation($mode=true)
    {
        $this->ignoreUnknownAnnotationMode = $mode;
        return $this;
    }

    public function getAllMetaData($class)
    {
        if($class instanceof ReflectionClass)
            $ref = $class;
        else
            $ref = new ReflectionClass($class);
        if($ref->isInternal())
            return false;
        $nameSpace = $ref->getNamespaceName();
        $className = $ref->name;
        $fileName  = $ref->getFileName();
        $lineNumber = $ref->getStartLine();
        $this->addImports($nameSpace,$className,$fileName);
        return $this->registMetaData($ref);
    }

    public function getClassAnnotations(ReflectionClass $ref)
    {
        if($ref->isInternal())
            return array();
        $metaData = $this->getAllMetaData($ref);
        if($metaData===false)
            return array();
        if(isset($metaData->classAnnotations))
            return $metaData->classAnnotations;
        else
            return array();
    }

    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        foreach ($this->getClassAnnotations($class) as $anno) {
            if ($anno instanceof $annotationName) {
                return $anno;
            }
        }
        return null;
    }

    public function getMethodAnnotations(ReflectionMethod $ref)
    {
        if($ref->isInternal())
            return array();
        $classRef  = $ref->getDeclaringClass();
        $metaData = $this->getAllMetaData($classRef);
        if($metaData===false)
            return array();
        if(isset($metaData->methodAnnotations[$ref->name]))
            return $metaData->methodAnnotations[$ref->name];
        else
            return array();
    }

    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        foreach ($this->getMethodAnnotations($method) as $anno) {
            if ($anno instanceof $annotationName) {
                return $anno;
            }
        }
        return null;
    }

    public function getPropertyAnnotations(ReflectionProperty $ref)
    {
        $classRef  = $ref->getDeclaringClass();
        if($classRef->isInternal())
            return array();
        $metaData = $this->getAllMetaData($classRef);
        if($metaData===false)
            return array();
        if(isset($metaData->fieldAnnotations[$ref->name]))
            return $metaData->fieldAnnotations[$ref->name];
        else
            return array();
    }

    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        foreach ($this->getPropertyAnnotations($property) as $anno) {
            if ($anno instanceof $annotationName) {
                return $anno;
            }
        }
        return null;
    }

    protected function getCache()
    {
        if(self::$metaDataCache)
            return self::$metaDataCache;

        if(self::$cachePath)
            $path = rtrim(self::$cachePath.'/\\').'/metadata';
        else
            $path = '/'.__CLASS__.'/metadata';

        self::$metaDataCache = CacheFactory::getInstance($path);
        return self::$metaDataCache;
    }

    protected function getImportsCache()
    {
        if(self::$importsCache)
            return self::$importsCache;

        if(self::$cachePath)
            $path = rtrim(self::$cachePath.'/\\').'/imports';
        else
            $path = '/'.__CLASS__.'/imports';

        self::$importsCache = CacheFactory::getInstance($path);
        return self::$importsCache;
    }

    protected function registAnnotationProvider($annotationClassName)
    {
        if(array_key_exists($annotationClassName, $this->annotationProvider))
            return $this->annotationProvider[$annotationClassName];

        $providerName = $annotationClassName.'Provider';
        if(!class_exists($providerName)) {
            $this->annotationProvider[$annotationClassName] = false;
            return false;
        }
        $provider = new $providerName();
        if(!$provider instanceof AnnotationProviderInterface) {
            $this->annotationProvider[$annotationClassName] = false;
            return false;
        }

        $this->annotationProvider[$annotationClassName] = $provider;
        foreach ($provider->getJoinPoints() as $method => $eventNames ) {
            foreach($eventNames as $eventName) {
                $this->events[$annotationClassName.'::'.$eventName][] = array($provider,$method);
            }
        }
        return $provider;
    }

    protected function executeAnnotationProvider($eventName,$annotationClassName,array $args)
    {
        $this->registAnnotationProvider($annotationClassName);
        $name = $annotationClassName.'::'.$eventName;
        if(!isset($this->events[$name]))
            return;
        foreach ($this->events[$name] as $listener) {
            $event = new Event($name,$args);
            call_user_func($listener,$event);
        }
    }

    public function createAnnotation($annotationName,$args,$location)
    {
        $className = $this->resolvAnnotationClass($annotationName,$location);
        if($className===false) {
            if($this->ignoreUnknownAnnotationMode)
                return false;
            throw new Exception\DomainException('a class is not found for the annotation:@'.$annotationName.' in '.$location['uri'].': '.$location['filename'].'('.$location['linenumber'].')');
        }

        $refClass = new ReflectionClass($className);
        if($refClass->isInternal()) {
            return false;
        }

        $metaData = $this->registMetaData($refClass,ElementType::ANNOTATION_TYPE);
        if($metaData) {
            $isAnnotation = false;
            foreach ($metaData->classAnnotations as $anno) {
                if($anno instanceof AnnotationTag) {
                    $isAnnotation = true;
                    break;
                }
            }
            if(!$isAnnotation) {
                throw new Exception\DomainException("the class is not annotation class.: ".$className.' in '.$location['uri'].': '.$location['filename'].'('.$location['linenumber'].')');
            }

            $this->executeClassAnnotationProvider($className,$metaData,$location);
        }

        if($args!==null) {
            if(is_array($args) && array_key_exists(0, $args)) {
                $value = $args;
                $args = array();
                $args['value'] = $value;
            } else if(!is_array($args)) {
                $value = $args;
                $args = array();
                $args['value'] = $value;
            }
            foreach($args as $field => $value) {
                $this->executeFieldAnnotationProvider($className,$field,$value,$metaData,$location);
            }
        }

        if(isset($metaData->hasConstructor) && $metaData->hasConstructor) {
            // Compatibility For Doctrine Annotation Reader
            $annotation = new $className($args);
        } else {
            // General Annotation
            $annotation = new $className();
            if($args!==null) {
                foreach($args as $field => $value) {
                    if(!property_exists($annotation, $field))
                        throw new Exception\DomainException('the argument "'.$field.'" is invalid for @'.$className.': in '.$location['uri'].': '.$location['filename'].'('.$location['linenumber'].')');
                    $annotation->{$field} = $value;
                }
            }
        }

        $this->initalizeAnnotationMetaData(
            $className,
            $annotation,
            $location
        );
        return $annotation;
    }

    public function addImports($nameSpace,$className,$fileName)
    {
        $importsCache = $this->getImportsCache();
        if(isset($importsCache[$className])) {
            return $this;
        }
        $nameSpaceExtractor = new NameSpaceExtractor($fileName);
        $importsCache[$className] = $nameSpaceExtractor->getImports($nameSpace);
        return $this;
    }

    public function resolvAnnotationClass($annotationName,$location)
    {
        if(substr($annotationName, 0, 1)=='\\') {
            if(class_exists($annotationName))
                return $annotationName;
            return false;
        }
        $importsCache = $this->getImportsCache();
        $class = $location['class'];
        if(isset($importsCache[$class])) {
            $imports = $importsCache[$class];
            $pieces = explode('\\',$annotationName);
            $alias = array_shift($pieces);
            if(isset($imports[$alias])) {
                $className = $imports[$alias];
                if(count($pieces))
                    $className .= '\\' . implode('\\', $pieces);
                if(class_exists($className)) {
                    return $className;
                }
            }
        }
        $pieces = explode('\\',$class);
        $className = $annotationName;
        array_pop($pieces);
        if(count($pieces))
            $className = implode('\\', $pieces) . '\\' . $className;
        if(class_exists($className))
            return $className;

        foreach($this->nameSpaces as $namespace) {
            $className = $namespace.'\\'.$annotationName;
            if(class_exists($className)) {
                return $className;
            }
        }
        return false;
    }

    protected function executeClassAnnotationProvider($className,$metaData,$location)
    {
        if(!isset($metaData->classAnnotations))
            return;
        foreach($metaData->classAnnotations as $classAnnotation) {
            $this->executeAnnotationProvider(
                AnnotationProviderInterface::EVENT_USED_PARENT,
                get_class($classAnnotation),
                array(
                    'annotationname'=> $className,
                    'metadata'  => $classAnnotation,
                    'location'  => $location,
                )
            );
        }
    }

    protected function executeFieldAnnotationProvider($className,$field,$value,$metaData,$location)
    {
        if(!isset($metaData->fieldAnnotations[$field]))
            return;
        foreach($metaData->fieldAnnotations[$field] as $fieldAnnotation) {
            $this->executeAnnotationProvider(
                AnnotationProviderInterface::EVENT_SET_FIELD,
                get_class($fieldAnnotation),
                array(
                    'annotationname'=> $className,
                    'fieldname' => $field,
                    'value'    => $value,
                    'metadata' => $fieldAnnotation,
                    'location' => $location
                )
            );
        }
    }

    protected function initalizeAnnotationMetaData($className,$annotation,$location)
    {
        $this->executeAnnotationProvider(
            AnnotationProviderInterface::EVENT_CREATED,
            get_class($annotation),
            array(
                'annotationname' => $className,
                'metadata' => $annotation,
                'location' => $location,
            )
        );
    }

	public function getMetaData($annotationClassName)
	{
		if(is_object($annotationClassName))
			$annotationClassName = get_class($annotationClassName);
		else if(!is_string($annotationClassName))
			throw new Exception\DomainException("the annotation must be a object or a class name.", 1);
			
		$metaDataCache = $this->getCache();
		if(!isset($metaDataCache[$annotationClassName]))
            return false;
    	return $metaDataCache[$annotationClassName];
	}

    protected function registMetaData(ReflectionClass $classRef,$type=ElementType::TYPE)
    {
        $annotationClassName = $classRef->name;
        $metaDataCache = $this->getCache();
        if(isset($metaDataCache[$annotationClassName])) {
            return $metaDataCache[$annotationClassName];
        }
        $metaData = $this->createMetaData($classRef,$type);
        if($metaData==false)
            return false;
        $metaDataCache[$annotationClassName] = $metaData;
        return $metaData;
    }

	protected function createMetaData(ReflectionClass $classRef,$elementType)
	{
        if($classRef->implementsInterface($this->dontRegistAnnotationInterface))
            return false;

        $nameSpace = $classRef->getNamespaceName();
        $className = $classRef->name;
        $fileName  = $classRef->getFileName();
        $lineNumber = $classRef->getStartLine();
        $this->addImports($nameSpace,$className,$fileName);
        $location = array(
            'target'   => $elementType,
            'class'    => $className,
            'name'     => $classRef->name,
            'uri'      => $classRef->name,
            'filename' => $fileName,
            'linenumber' => $lineNumber,
        );

		$metaData = new AnnotationMetaData();
		$metaData->className = $classRef->name;
        $metaData->hasConstructor = ($classRef->getConstructor()) ? true : false;
		$metaDataFileName  = $classRef->getFileName();
		$metaData->classAnnotations = $this->createClassAnnotations($classRef,$location,$elementType);

        $propRefs = $classRef->getProperties();
        foreach ($propRefs as $propRef) {
        	$annos = $this->createPropertyAnnotations($propRef,$location);
            foreach($annos as $anno) {
                $fieldName = $propRef->getName();
                if(!isset($metaData->fieldAnnotations[$fieldName]))
                    $metaData->fieldAnnotations[$fieldName] = array();
                $metaData->fieldAnnotations[$fieldName][] = $anno;
            }
        }
        $methodRefs = $classRef->getMethods();
        foreach ($methodRefs as $methodRef) {
			$annos = $this->createMethodAnnotations($methodRef,$location);
            foreach($annos as $anno) {
                $methodName = $methodRef->getName();
                if(!isset($metaData->methodAnnotations[$methodName]))
                    $metaData->methodAnnotations[$methodName] = array();
                $metaData->methodAnnotations[$methodName][] = $anno;
            }
        }
        return $metaData;
	}

    public function createClassAnnotations(ReflectionClass $ref,$location, $elementType=ElementType::TYPE)
    {
        if($ref->isInternal())
            return array();
        $location['target'] = $elementType;
        $location['name']   = $ref->name;
        $location['uri']    = $ref->name;
        return $this->parser->searchAnnotation($ref->getDocComment(),$location);
    }

    public function createPropertyAnnotations(ReflectionProperty $ref,$location)
    {
        $classRef  = $ref->getDeclaringClass();
        if($classRef->isInternal()) {
            return array();
        }
        $location['target'] = ElementType::FIELD;
        $location['name']   = $ref->name;
        $location['uri']    = $ref->class.'::$'.$ref->name;
        return $this->parser->searchAnnotation($ref->getDocComment(),$location);
    }

    public function createMethodAnnotations(ReflectionMethod $ref,$location)
    {
        if($ref->isInternal()) {
            return array();
        }
        $location['target'] = ElementType::METHOD;
        $location['name']   = $ref->name;
        $location['uri']    = $ref->class.'::'.$ref->name.'()';
        return $this->parser->searchAnnotation($ref->getDocComment(),$location);
    }
}