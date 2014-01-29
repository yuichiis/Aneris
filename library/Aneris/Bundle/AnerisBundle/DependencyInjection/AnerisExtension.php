<?php
namespace Aneris\Bundle\AnerisBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Aneris\Bundle\AnerisBundle\Exception;

class AnerisExtension extends Extension
{
    const PARAMETER_MODULE_MANAGER_CONFIG = 'aneris.container.module_manager.config_path';
    const CONFIG_PATH = 'config_path';

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        if(isset($config[self::CONFIG_PATH])) {
            if(!file_exists($config[self::CONFIG_PATH]))
                throw new Exception\DomainException('file not exist('.$config[self::CONFIG_PATH].'). please check application config.');
                
            $container->setParameter(
                self::PARAMETER_MODULE_MANAGER_CONFIG, 
                $config[self::CONFIG_PATH]);
        }
    }

    public function getAlias()
    {
        return 'aneris';
    }
}
