<?php
namespace Aneris\Form;

use Aneris\Container\ServiceLocatorInterface;

class FormContextBuilderFactory
{
    const DEFAULT_VALIDATOR_SERVICE  = 'Aneris\Validator\Validator';

    public static function factory(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        if(isset($config['form']))
            $config = $config['form'];
        else
            $config = null;
        $annotationReader = null;
        $validator = null;
        if(isset($config['annotation_reader']))
            $annotationReader = $serviceLocator->get($config['annotation_reader']);
        if(isset($config['validator'])) {
            $validatorName = $config['validator'];
        } else {
            $validatorName = self::DEFAULT_VALIDATOR_SERVICE;
        }
        if($serviceLocator->has($validatorName))
            $validator = $serviceLocator->get($validatorName);

        $formContext = new FormContextBuilder($annotationReader,$validator);

        return $formContext;
    }
}