<?php
namespace Aneris\Mvc\Router;

use Aneris\Mvc\Exception;

class LiteralParser
{
    public static function parse($path, $route)
    {
        $paramStr = substr($path,strlen($route['path']));
        if($paramStr===false)
            return array();
        $paramArray = explode('/',rtrim(ltrim($paramStr,'/'),'/'));
        if(count($paramArray)==0)
            return array();
        throw new Exception\PageNotFoundException('A literal route has sub-directory on route "'.$route['route_name'].'".');
    }

    public static function assemble(array $param,array $route)
    {
        if(count($param))
            throw new Exception\DomainException('a literal route can not have parameters:'.implode(',',$param));
        return $route['path'];
   }
}
