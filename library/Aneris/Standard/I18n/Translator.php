<?php
namespace Aneris\Standard\I18n;

interface Translator
{
    public function translate($message, $domain=null, $locale=null);
}
