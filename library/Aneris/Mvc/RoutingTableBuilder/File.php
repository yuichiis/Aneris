<?php
namespace Aneris\Mvc\RoutingTableBuilder;

use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Mvc\RoutingTableBuilderInterface;
use Aneris\Mvc\Exception;

class File implements RoutingTableBuilderInterface
{
    protected static $loaderAliases = array(
        'yaml' => 'yaml_parse_file',
    );
    protected $config;
    protected $routes = array();

    public function setConfig(array $config=null)
    {
        $this->config = $config;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function build(array $typeAndfiles=null)
    {
        if($typeAndfiles==null) {
            if(!isset($this->config['config_files']))
                throw new Exception\DomainException('a path of configuration file is not specified.');
            $typeAndfiles = $this->config['config_files'];
        }

        foreach ($typeAndfiles as $type => $files) {
            foreach ($files as $file => $switch) {
                if(!$switch)
                    continue;
                if(!file_exists($file))
                    throw new Exception\DomainException('mvc routing configuration file is not exist.: '. $file);
                if($type==='php') {
                    $config = include $file;
                }
                else {
                    if(isset(self::$loaderAliases[$type]))
                        $loader = self::$loaderAliases[$type];
                    else
                        $loader = $type;
                    if(!is_callable($loader))
                        throw new Exception\DomainException('a loader is not found.: '. $type);
                    $config = call_user_func($loader,$file);
                }
                if(!is_array($config))
                    throw new Exception\DomainException('invalid return type or loading error.: '. $file);
                $this->routes = array_merge_recursive($this->routes,$config);
            }
        }
        return $this;
    }
}
