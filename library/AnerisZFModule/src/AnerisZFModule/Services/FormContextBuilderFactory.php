<?php
namespace AnerisZFModule\Services;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Aneris\Form\FormContextBuilder;

class FormContextBuilderFactory implements FactoryInterface
{
    const DEFAULT_VALIDATOR_SERVICE  = 'Aneris\Validator\Validator';

    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $config = $serviceManager->get('Config');
        if(isset($config['form']))
            $config = $config['form'];
        else
            $config = array();
        $annotationReader = null;
        $validator = null;
        if(isset($config['annotation_reader']))
            $annotationReader = $serviceManager->get($config['annotation_reader']);
        if(isset($config['validator'])) {
            $validatorName = $config['validator'];
        } else {
            $validatorName = self::DEFAULT_VALIDATOR_SERVICE;
        }
        if($serviceManager->has($validatorName))
            $validator = $serviceManager->get($validatorName);

        $formContext = new FormContextBuilder($annotationReader,$validator);

        return $formContext;
    }
}