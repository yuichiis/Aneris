<?php
namespace Aneris\Http;

class Request implements HttpRequestInterface
{
    protected $pathPrefix;
    protected $rootPath;
    protected $path;

    public function getServerUrl()
    {
        $scheme = $this->getScheme();
        $server = $this->getServer();
        return $scheme.'://'.$server;
    }

    public function getServer()
    {
        if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        return $_SERVER['SERVER_NAME'];
    }

    public function getScheme()
    {
        if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true))
            return 'https';
        if(isset($_SERVER['HTTP_SCHEME']) && ($_SERVER['HTTP_SCHEME'] == 'https'))
            return 'https';
        if($this->getPort()===443)
            return 'https';

        return 'http';
    }

    public function getPort()
    {
        if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']) {
            return $_SERVER['SERVER_PORT'];
        }
        return 0;
    }

    public function getPathPrefix()
    {
        if($this->pathPrefix)
            return $this->pathPrefix;

        $path = '';
        if(isset($_SERVER['SCRIPT_NAME'])) {
            $path = dirname($_SERVER['SCRIPT_NAME']);
            if($path==='\\' || $path==='/')
                $path='';
        }
        return $this->pathPrefix = $path;
    }

    public function getRootPath()
    {
        if($this->rootPath)
            return $this->rootPath;

        if(!isset($_SERVER['SCRIPT_NAME']) || !isset($_SERVER['REQUEST_URI'])) {
            return '';
        }
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $requestUri = $_SERVER['REQUEST_URI'];
        if(substr($requestUri,0,strlen($scriptName))==$scriptName)
            return $scriptName;
        $path = dirname($scriptName);
        if($path==='\\' || $path==='/')
            $path='';
        return $this->rootPath = $path;
    }

    public function getPath()
    {
        if($this->path)
            return $this->path;

        $path = '';
        if(isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
            $start = strlen($_SERVER['SCRIPT_NAME']);
            if(strpos($uri,$_SERVER['SCRIPT_NAME'])!==0) {
                $start = strlen($this->getPathPrefix());
            }
            $pos = strpos($uri,'?');
            if($pos===false) {
                $path = substr($uri,$start);
            } else {
                $path = substr($uri,$start,$pos-$start);
            }
            if(strlen($path)==0)
                $path = '/';
        }
        return $this->path = $path;
    }

    public function getUri()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
    }

    public function getMethod()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
    }

    public function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST';
    }

    public function getPosts()
    {
        return isset($_POST) ? $_POST : null;
    }

    public function getPost($name, $default=null)
    {
        if(array_key_exists($name,$_POST))
            return $_POST[$name];
        return $default;
    }

    public function getQueries()
    {
        return isset($_GET) ? $_GET : null;
    }

    public function getQuery($name, $default=null)
    {
        if(array_key_exists($name,$_GET))
            return $_GET[$name];
        return $default;
    }

    public function getHeaders()
    {
        if(!isset($_SERVER))
            return null;
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if(substr($key,0,5)=='HTTP_') {
                $parts = explode('_', substr($key,5));
                $partsNew = array();
                foreach($parts as $part) {
                    $partsNew[] = ucfirst(strtolower($part));
                }
                $name = implode('-',$partsNew);
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    public function getHeader($name, $default=null)
    {
        if(!isset($_SERVER))
            return null;
        $key = 'HTTP_'.str_replace('-','_',strtoupper($name));
        if(array_key_exists($key ,$_SERVER))
            return $_SERVER[$key];
        return $default;
    }
}
