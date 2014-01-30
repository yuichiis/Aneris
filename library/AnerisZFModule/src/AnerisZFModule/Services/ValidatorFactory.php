<?php
namespace AnerisZFModule\Services;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Aneris\Validator\Validator;
use Aneris\Validator\ConstraintContextBuilder;

class ValidatorFactory implements FactoryInterface
{
    const DEFAULT_TRANSLATOR_SERVICE = 'MvcTranslator';
    const DEFAULT_ANNOTATION_READER  = 'Aneris\Annotation\AnnotationManager';

    public function createService(ServiceLocatorInterface $serviceManager)
    {
        $translator = null;
        $constraintManager = null;
        $translatorTextDomain = null;
        $translatorName = null;
        $annotationReaderName = null;
        $config = $serviceManager->get('Config');
        if(isset($config['validator'])) {
            $config = $config['validator'];
            if(isset($config['annotation_reader']))
                $annotationReaderName = $config['annotation_reader'];
            if(isset($config['translator']))
                $translatorName = $config['translator'];
            if(isset($config['translator_text_domain']))
                $translatorTextDomain = $config['translator_text_domain'];
        }
        if($annotationReaderName) {
            $annotationReader = $serviceManager->get($annotationReaderName);
        } else {
            if($serviceManager->has(self::DEFAULT_ANNOTATION_READER)) {
                $annotationReader = $serviceManager->get(self::DEFAULT_ANNOTATION_READER);
            } else {
                $annotationReaderName = self::DEFAULT_ANNOTATION_READER;
                $annotationReader = $annotationReaderName::factory();
            }
        }
        $constraintManager = new ConstraintContextBuilder(null,$annotationReader);

        if($translatorName) {
            $translator = $serviceManager->get($translatorName);
        } else {
            if($serviceManager->has(self::DEFAULT_TRANSLATOR_SERVICE)) {
                $translator = $serviceManager->get(self::DEFAULT_TRANSLATOR_SERVICE);
                $translatorTextDomain = Validator::getTranslatorTextDomain();
            }
        }

        $validator = new Validator($translator,$constraintManager);
        if($translatorTextDomain)
            $validator->setTranslatorTextDomain($translatorTextDomain);
        return $validator;
    }
}
