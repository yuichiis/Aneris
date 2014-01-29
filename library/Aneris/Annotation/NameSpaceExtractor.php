<?php
namespace Aneris\Annotation;

if(!defined('T_TRAIT'))
    define('T_TRAIT','T_TRAIT');

class NameSpaceExtractor
{
    protected $filename;
    protected $token;
    protected $parsed;
    protected $tokens;
    protected $imports;
    protected $classes;

    public function __construct($filename)
    {
        if(empty($filename))
            throw new Exception\DomainException('Filename cannot be empty');
        $this->filename = $filename;
    }

    public function getAllClass()
    {
        if(!$this->parsed) {
            $this->parse();
            $this->parsed = true;
        }
        return $this->classes;
    }

    public function getImports($nameSpace)
    {
        if(!$this->parsed) {
            $this->parse();
            $this->parsed = true;
        }
        if(isset($this->imports[$nameSpace]))
            return $this->imports[$nameSpace];
        return array();
    }

    public function getAllImports()
    {
        if(!$this->parsed) {
            $this->parse();
            $this->parsed = true;
        }
        return $this->imports;
    }

    protected function getContent()
    {
        return file_get_contents($this->filename);
    }

    protected function getToken($skipSpace=true)
    {
        if($this->tokens==null)
            $this->tokens = token_get_all($this->getContent());

        while(true) {
            $token = current($this->tokens);
            if(is_array($token)) {
                $code = $token[0];
                $text = $token[1];
            } else {
                $code = $token;
                $text = $token;
            }
            if($code!=T_WHITESPACE || !$skipSpace)
                break;
            next($this->tokens);
        }
        return array($code,$text);
    }

    protected function nextToken()
    {
        next($this->tokens);
    }

    protected function parse($statement='namespace',$currentNameSpace='',$endcode=null)
    {
        while(true) {
            list($code,$text) = $this->getToken();
            if($code==false)
                break;
            if($code==$endcode) {
                $this->nextToken();
                break;
            }
            switch ($code) {
                case T_NAMESPACE:
                    $this->nextToken();
                    $this->parseNameSpaceStatement($currentNameSpace);
                    break;

                case T_USE:
                    $this->nextToken();
                    if($statement=='namespace')
                        $this->parseUseStatement($currentNameSpace);
                    break;

                case T_CLASS:
                    $this->nextToken();
                    $this->parseClassStatement($currentNameSpace);
                    break;

                case T_TRAIT:
                case T_FUNCTION:
                    $this->nextToken();
                    $statement = $text;
                    break;

                case '{':
                    $this->nextToken();
                    $this->parse($statement,$currentNameSpace,'}');
                    break;

                default:
                    $this->nextToken();
                    break;
            }
        }
    }

    protected function parseName()
    {
        $name = '';
        list($code,$text) = $this->getToken(true);
        if($code==false)
            return $name;
        while(true) {
            list($code,$text) = $this->getToken(false);
            if($code==false)
                return $name;
            if($code != T_STRING && $code != T_NS_SEPARATOR)
                break;
            $name .= $text;
            $this->nextToken();
        }
        return $name;
    }

    protected function parseNames()
    {
        $names = array();
        while(true) {
            $name = $this->parseName();
            if(empty($name))
                break;
            list($code,$text) = $this->getToken();
            if($code!=',')
                break;
            $this->nextToken();
        }
        return $names;
    }

    protected function parseNameSpaceStatement($currentNameSpace)
    {
        $name = $this->parseName();
        if(!empty($name)) {
            if(substr($name,0,1)=='\\' || empty($currentNameSpace))
                $currentNameSpace = trim($name,'\\');
            else
                $currentNameSpace .= '\\'.trim($name,'\\');
        } else {
            $currentNameSpace = '';
        }

        list($code,$text) = $this->getToken();
        switch ($code) {
            case '{':
                $this->nextToken();
                $this->parse('namespace',$currentNameSpace,'}');
                break;
            case ';':
                $this->nextToken();
                $this->parse('namespace',$currentNameSpace,false);
                break;
            default:
                throw new Exception\DomainException('namespace syntax error in '.$this->filename);
        }
    }

    protected function parseUseStatement($currentNameSpace)
    {
        while(true) {
            $name = $this->parseName();
            if(!empty($name)) {
                $importName = trim($name,'\\');
            } else {
                throw new Exception\DomainException('use statement syntax error "'.$text.'" after "use" in '.$this->filename);
            }
            $alias = null;
            list($code,$text) = $this->getToken();
            if($code==T_AS) {
                $this->nextToken();
                list($code,$text) = $this->getToken();
                if($code!=T_STRING)
                    throw new Exception\DomainException('use statement syntax error "'.$text.'" after "as" in '.$this->filename);
                $alias = $text;
                $this->nextToken();
                list($code,$text) = $this->getToken();
            }
            $this->addImport($currentNameSpace,$importName,$alias);
            if($code==';') {
                $this->nextToken();
                break;
            }
            else if($code!=',') {
                $syntax = $text;
                throw new Exception\DomainException('use statement syntax error "'.$syntax.'". it not found ";" or "," in '.$this->filename);
            }
            // $code==','
            $this->nextToken();
        }
    }

    protected function addImport($currentNameSpace,$importName,$alias)
    {
        if(empty($currentNameSpace))
            $currentNameSpace = '__TOPLEVEL__';
        if($alias==null) {
            $alias = basename(str_replace('\\','/',$importName));
        }
        $this->imports[$currentNameSpace][$alias] = $importName;
    }

    protected function parseClassStatement($currentNameSpace)
    {
        $name = $this->parseName();
        if(!empty($name)) {
            if(substr($name,0,1)=='\\' || empty($currentNameSpace))
                $className = trim($name,'\\');
            else
                $className = $currentNameSpace . '\\'.trim($name,'\\');
        } else {
            throw new Exception\DomainException('class syntax error in '.$this->filename);
        }

        while(true) {
            list($code,$text) = $this->getToken();
            switch ($code) {
                case '{':
                    $this->nextToken();
                    $this->parse('class',$className,'}');
                    $this->addClass($className);
                    return;
                case T_EXTENDS:
                case T_IMPLEMENTS:
                    $this->nextToken();
                    $this->parseNames();
                    break;
                case T_STRING:
                    $this->nextToken();
                    break;
                default:
                    $syntax = $text;
                    throw new Exception\DomainException('syntax error "'.$syntax.'" in class "'.$className.'": '.$this->filename);
            }
        }
    }

    protected function addClass($className)
    {
        $this->classes[] = $className;
    }
}
