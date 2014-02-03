<?php
namespace Aneris\Stdlib\I18n;

class Translator
{
    protected static $initialized;

    protected $currentDomain = 'messages';
    protected $currentLocale = 'C';
    protected $gettext;

    public function __construct()
    {
        $this->gettext = Gettext::factory();
    }

    public function bindTextDomain($domain, $path)
    {
        $this->gettext->bindTextDomain($domain,$path);
        return $this;
    }
/*
    public function bindTextDomainCodeset($domain, $codeset)
    {
        bind_textdomain_codeset($domain,$codeset);
        return $this;
    }
*/
    public function setLocale($locale)
    {
        $this->currentLocale = $locale;
        return $this;
    }

    public function getLocale()
    {
        return $this->currentLocale;
    }

    public function setTextDomain($domain)
    {
        $this->currentDomain = $domain;
        return $this;
    }

    public function setup($domain=null,$path=null,$locale=null)
    {
        if($locale!==null)
            $this->setLocale($locale);
        if($domain!==null && $path!=null)
            $this->bindTextDomain($domain,$path);
        if($domain!==null)
            $this->setTextDomain($domain);
        return $this;
    }

    public function translate($message, $domain=null, $locale=null)
    {
        if($domain==null)
            $domain = $this->currentDomain;
        if($locale==null)
            $locale = $this->currentLocale;

        $result = $this->gettext->getText($message,$domain,$locale);
        return $result;
    }
/*
    public function translatePlural($singular, $plural, $number, $domain=null, $locale=null)
    {
        if($domain==null)
            $domain = $this->currentDomain;
        if($locale==null)
            $locale = $this->currentLocale;
 
        $result = $this->gettext->ngettext($singular, $plural, $number, $domain, $locale);
        return $result;
    }
*/
}