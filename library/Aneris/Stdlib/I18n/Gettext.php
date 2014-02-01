<?php
namespace Aneris\Stdlib\I18n;

class Gettext
{
    const ENVIRONMENT_VAR = "LC_MESSAGES";
    protected static $initialized;

    protected $currentDomain = 'messages';
    protected $currentLocale = false;

    public static function initialize()
    {
        if(self::$initialized)
            return;
        self::$initialized = true;

        $current = getenv("LC_ALL");
        if($current===false)
            return;

        putenv("LC_ALL");
        if(getenv("LC_COLLATE")===false)
            putenv("LC_COLLATE=".$current);
        if(getenv("LC_CTYPE")===false)
            putenv("LC_CTYPE=".$current);
        if(getenv("LC_MONETARY")===false)
            putenv("LC_MONETARY=".$current);
        if(getenv("LC_NUMERIC")===false)
            putenv("LC_NUMERIC=".$current);
        if(getenv("LC_TIME")===false)
            putenv("LC_TIME=".$current);
        if(getenv("LC_MESSAGES")===false)
            putenv("LC_MESSAGES=".$current);
        if(defined("LC_MESSAGES")) {
            setlocale(LC_ALL, null);
            setlocale(LC_MESSAGES, null);
        }
    }

    public function __construct()
    {
        self::initialize();
        $this->currentDomain = textdomain(null);
        $this->currentLocale = getenv(self::ENVIRONMENT_VAR);
    }

    public function bindTextDomain($domain, $path)
    {
        bindtextdomain($domain,$path);
        return $this;
    }

    public function bindTextDomainCodeset($domain, $codeset)
    {
        bind_textdomain_codeset($domain,$codeset);
        return $this;
    }

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

    private function adjustEnvironment()
    {
        if(getenv(self::ENVIRONMENT_VAR) !== $this->currentLocale && $this->currentLocale!==null) {
            if($this->currentLocale===false) {
                putenv(self::ENVIRONMENT_VAR);
                if(defined("LC_MESSAGES")) {
                    setlocale(LC_MESSAGES, null);
                }
            }
            else {
                putenv(self::ENVIRONMENT_VAR.'='.$this->currentLocale);
                if(defined("LC_MESSAGES")) {
                    setlocale(LC_MESSAGES, $this->currentLocale);
                }
            }
        }

        if(textdomain(null) !== $this->currentDomain && $this->currentDomain!==null) {
            textdomain($this->currentDomain);
        }
    }

    public function translate($message, $domain=null, $locale=null)
    {
        if($locale) {
            $backupLocale = $this->currentLocale;
            $this->currentLocale = $locale;
        }
        if($domain) {
            $backupDomain = $this->currentDomain;
            $this->currentDomain = $domain;
        }

        $this->adjustEnvironment();
        $result = gettext($message);

        if($locale) {
            $this->currentLocale = $backupLocale;
        }
        if($domain) {
            $this->currentDomain = $backupDomain;
        }

        return $result;
    }

    public function translatePlural($singular, $plural, $number, $domain=null, $locale=null)
    {
        if($locale) {
            $backupLocale = $this->currentLocale;
            $this->currentLocale = $locale;
        }
        if($domain) {
            $backupDomain = $this->currentDomain;
            $this->currentDomain = $domain;
        }

        $this->adjustEnvironment();
        $result = ngettext($singular, $plural, $number, $domain=null, $locale=null);

        if($locale) {
            $this->currentLocale = $backupLocale;
        }
        if($domain) {
            $this->currentDomain = $backupDomain;
        }
        return $result;
    }
}