<?php
namespace Aneris\Annotation;

use Reflector;
use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;
use Aneris\Annotation\Annotations\Annotation as AnnotationTag;
use Aneris\Annotation\Annotations\Target as TargetTag;

class Parser
{
    private static $ignoredTags = array(
        // Documentor tags
        'abstract'=> true, 'access'=> true, 'api' => true, 'author'=> true, 
        'category'=> true, 'copyright'=> true, 'deprecated'=> true, 'example'=> true, 
        'final'=> true, 'filesource'=> true, 'global'=> true, 'ignore'=> true, 
        'internal'=> true, 'license'=> true, 'inheritdoc' => true, 'link'=> true,
        'method' => true, 'name'=> true, 'package'=> true, 'param'=> true, 'property' => true, 
        'propertyread' => true, 'property-read' => true, 'propertywrite' => true,
        'property-write' => true,'return'=> true, 'see'=> true,'since'=> true,
        'source'=> true, 'static'=> true, 'staticvar'=> true, 'subpackage'=> true,
        'throws'=> true, 'throw' => true, 'todo'=> true, 'tutorial'=> true, 'uses' => true,
        'usedby'=> true, 'used-by'=> true, 'var'=> true, 'version'=> true,
        'magic' => true, 'serial'=> true, 'exception'=> true,
        // Unsupported Java Annotation
        'Retention' => true, 'Documented' => true, 

    );

    protected $manager;

    public function __construct(AnnotationManager $manager)
    {
       $this->manager = $manager;
    }


    public function searchAnnotation($doc,$location)
    {
        $lexer = new Lexer($doc,$location);
        $first = true;
        $annotations = array();
        while(true) {
            list($code,$text) = $lexer->get(false);
            if($code===false)
                break;
            switch($code) {
                case T_WHITESPACE:
                case T_OPEN_TAG:
                case T_COMMENT:
                    $lexer->next();
                    if(strpos($text, "\n")!==false) {
                        $first = true;
                    }
                    break;
                case '@':
                    $lexer->next();
                    if(!$first) {
                        break;
                    }
                    //list($code,$text) = $lexer->get(false);
                    //$lexer->next();
                    //if($code!='*')
                    //    break;
                    //list($code,$text) = $lexer->get(false);
                    //$lexer->next();
                    //if($code!=T_WHITESPACE || strpos($text, "\n")!==false)
                    //    break;
                    $anno = $this->getAnnotation($lexer);
                    if($anno!==false) {
                        $annotations[] = $anno;                        
                    }
                    break;
                default:
                    $first = false;
                    $lexer->next();
                    break;
            }
        }
        return $annotations;

    }

    public function getAnnotation($lexer)
    {
        $annotationName = '';
        while(true) {
            list($code,$text) = $lexer->get(false);
            switch($code) {
                case T_CONSTANT_ENCAPSED_STRING:
                case T_STRING:
                case T_NS_SEPARATOR:
                case T_NAMESPACE:
                case T_USE:
                    $annotationName .= $text;
                    break;
                case '(':
                case T_WHITESPACE:
                    if($code=='(') {
                        $lexer->next();
                        $args = $this->getList($lexer,')');
                    } else {
                        $args = null;
                    }
                    if(is_array($args)) {
                        if(count($args)==0)
                            $args = null;
                        if(count($args)==1 && array_key_exists(0, $args))
                            $args = $args[0];
                    }
                    if($this->isIgnoredName($annotationName))
                        return false;
                    $annotaion = $this->manager->createAnnotation($annotationName,$args,$lexer->getLocation());
                    return $annotaion;
                default:
                    $syntax = '@'.$annotationName.$text;
                    $location = $lexer->getLocation();
                    throw new Exception\DomainException('Syntax error "'.$syntax.'" in annotation name.:'.$location['uri'].':'.$location['filename'].'('.$location['linenumber'].')');
            }
            $lexer->next();
        }
    }

    public function isIgnoredName($name)
    {
        return isset(self::$ignoredTags[$name]);
    }

    public function getList($lexer,$endcode)
    {
        $structure = array();
        list($code,$text) = $lexer->get();
        if($code === $endcode) {
            $lexer->next();
            return $structure;
        }
        while(true) {
            switch($code) {
                case T_CONSTANT_ENCAPSED_STRING:
                case T_STRING:
                case T_LNUMBER:
                    $lexer->next();
                    list($tmpCode,$tmpText) = $lexer->get();
                    if($tmpCode!='=') {
                        $structure[] = $this->convertType($code,$text);
                        break;
                    }
                    $lexer->next();
                    $structure[$text] = $this->getValue($lexer,$endcode);
                    break;
                case '@':
                    $lexer->next();
                    $structure[] = $this->getAnnotation($lexer);
                    break;
                case '{':
                    $lexer->next();
                    $structure[] = $this->getList($lexer,'}');
                    break;
                default:
                    $location = $lexer->getLocation();
                    $syntax = $text;
                    throw new Exception\DomainException('Syntax error "'.$syntax.'" in list.:'.$location['uri'].':'.$location['filename'].'('.$location['linenumber'].')');
            }
            list($code,$text) = $lexer->get();
            if($code === $endcode) {
                $lexer->next();
                return $structure;
            } else if($code == ',') {
                $lexer->next();
                list($code,$text) = $lexer->get();
            } else {
                if(is_numeric($code))
                    $syntax = $text;
                else
                    $syntax = $code;
                $location = $lexer->getLocation();
                throw new Exception\DomainException('Syntax error "'.$syntax.'" in list.:'.$location['uri'].':'.$location['filename'].'('.$location['linenumber'].')');
            }
        }
    }

    public function getValue($lexer)
    {
        list($code,$text) = $lexer->get();
        switch($code) {
            case T_CONSTANT_ENCAPSED_STRING:
            case T_STRING:
            case T_LNUMBER:
                $lexer->next();
                return $this->convertType($code,$text);
            case '{':
                $lexer->next();
                return $this->getList($lexer,'}');
            default:
                $syntax = $text;
                $location = $lexer->getLocation();
                throw new Exception\DomainException('Syntax error "'.$syntax.'" in value.:'.$location['uri'].':'.$location['filename'].'('.$location['linenumber'].')');
        }
    }

    protected function convertType($code,$text)
    {
        switch($code) {
            case T_STRING:
                if(strcasecmp($text,'true')==0)
                    return true;
                if(strcasecmp($text,'false')==0)
                    return false;
        }
        return $text;
    }
}
