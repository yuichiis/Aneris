<?php
namespace Aneris\Module\Twig;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Aneris\Container\ServiceLocatorInterface;

class TwigFactory
{
    public static function factory(ServiceLocatorInterface $serviceManager)
    {
        $loadedExtensions = array();

        $config = $serviceManager->get('config');
        $config = $config['twig'];
        if(isset($config['template_paths']))
            $paths = $config['template_paths'];
        else
            $paths = array();
        $loader = new Twig_Loader_Filesystem($paths);
        $twig = new Twig_Environment($loader, $config);

        if(isset($config['extensions'])) {
            foreach($config['extensions'] as $extension) {
                if(isset($loadedExtensions[$extension]))
                    continue;
                if(class_exists($extension)) {
                    $twig->addExtension(new $extension($serviceManager));
                }
                $loadedExtensions[$extension] = true;
            }
        }

        return $twig;
    }
}