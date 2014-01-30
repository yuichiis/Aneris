<?php
use Aneris\Validator\Validator;

return array(
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type'        => 'Gettext',
                'base_dir'    => Validator::getTranslatorBasePath(),
                'pattern'     => Validator::getTranslatorFilePattern(),
            ),
        ),
    ),
);