<?php
namespace Aneris\Mvc\Router;

use Aneris\Mvc\Exception;

class SegmentParser
{
    public static function parse($path, $route)
    {
        $param = array();
        if(!isset($route['options']['parameters']))
            return $param;
        $paramStr = substr($path,strlen($route['path']));
        if($paramStr===false)
            return $param;
        $paramArray = explode('/',rtrim(ltrim($paramStr,'/'),'/'));
        $idx = 0;
        foreach($route['options']['parameters'] as $name) {
            if(isset($paramArray[$idx]))
                $param[$name] = $paramArray[$idx];
            $idx++;
        }
        return $param;
    }

    public static function assemble(array $param,array $route)
    {
        if(!isset($route['options']['parameters']))
            return $route['path'];
        $path = '';
        foreach($route['options']['parameters'] as $name) {
            if(!isset($param[$name]))
                break;
            $path .= '/'.$param[$name];
            unset($param[$name]);
        }
        if(count($param))
            throw new Exception\DomainException('unknown parameter:'.implode(',',array_keys($param)));
        $path = rtrim($route['path'],'/').$path;
        if($path==='')
            return '/';
        return $path;
    }
}
