<?php
namespace Aneris\Stdlib\I18n;

use Aneris\Container\ServiceLocatorInterface;

class GettextFactory
{
    public static function factory($serviceManager)
    {
        $config = $serviceManager->get('config');
        $translator = new Gettext();
        if(isset($config['translator'])) {
            $config = $config['translator'];

            if(isset($config['translation_file_patterns'])) {
                foreach ($config['translation_file_patterns'] as $pattern) {
                    if(!isset($pattern['type']) || strtolower($pattern['type'])!='gettext')
                        continue;
                    if(!isset($pattern['text_domain']) || !isset($pattern['base_dir']))
                        continue;
                    $translator->bindTextDomain($pattern['text_domain'],$pattern['base_dir']);
                }
            }
        }
        return $translator;
    }
}