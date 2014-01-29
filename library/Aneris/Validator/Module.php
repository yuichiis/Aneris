<?php
namespace Aneris\Validator;

class Module
{
    public function getConfig()
    {
        return array(
            'container' => array(
                'aliases' => array(
                    'Aneris\Validator\ValidatorService' => 'Aneris\Validator\Validator',
                ),
                'components' => array(
                    'Aneris\Validator\Validator' => array(
                        'constructor_args' => array(
                            'translator' => array('ref' => 'Aneris\Stdlib\I18n\GettextService'),
                            'constraintManager' => array('ref' => 'Aneris\Validator\ConstraintContextBuilder'),
                        ),
                        'properties' => array(
                            'translatorTextDomain' => array('value' => 'validator'),
                        ),
                    ),
                    'Aneris\Validator\ConstraintContextBuilder' => array(
                        // it will be able to inject annotation reader here.
                    ),
                ),
            ),
            'translator' => array(
                'translation_file_patterns' => array(
                    __NAMESPACE__ => array(
                        'type'        => 'Gettext',
                        'base_dir'    => Validator::getTranslatorBasePath(),
                        'pattern'     => Validator::getTranslatorFilePattern(),
                        'text_domain' => Validator::getTranslatorTextDomain(),
                    ),
                ),
            ),
        );
    }
}
