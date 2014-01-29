<?php
namespace Aneris\Validator;

use Aneris\Container\ServiceLocatorInterface;

class ValidatorFactory
{
    const DEFAULT_TRANSLATOR_SERVICE = 'Aneris\Stdlib\I18n\Gettext';
    const DEFAULT_ANNOTATION_READER  = 'Aneris\Annotation\AnnotationManager';

    public static function factory($serviceLocator)
    {
        $translator = null;
        $constraintManager = null;
        $translatorTextDomain = null;
        $translatorName = null;
        $annotationReaderName = null;
        $config = $serviceLocator->get('config');
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
            $annotationReader = $serviceLocator->get($annotationReaderName);
        } else {
            if($serviceLocator->has(self::DEFAULT_ANNOTATION_READER)) {
                $annotationReader = $serviceLocator->get(self::DEFAULT_ANNOTATION_READER);
            } else {
                $annotationReaderName = self::DEFAULT_ANNOTATION_READER;
                $annotationReader = $annotationReaderName::factory();
            }
        }
        $constraintManager = new ConstraintContextBuilder(null,$annotationReader);

        if($translatorName) {
            $translator = $serviceLocator->get($translatorName);
        } else {
            if($serviceLocator->has(self::DEFAULT_TRANSLATOR_SERVICE)) {
                $translator = $serviceLocator->get(self::DEFAULT_TRANSLATOR_SERVICE);
                $translatorTextDomain = Validator::getTranslatorTextDomain();
            }
        }

        $validator = new Validator($translator,$constraintManager);
        if($translatorTextDomain)
            $validator->setTranslatorTextDomain($translatorTextDomain);
        return $validator;
    }
}