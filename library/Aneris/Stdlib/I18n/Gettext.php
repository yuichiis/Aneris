<?php
namespace Aneris\Stdlib\I18n;
class Gettext
{
    const MAGIC_BIGENDIAN    = '950412de';
    const MAGIC_LITTLEENDIAN = 'de120495';

    static protected $instance;
    protected $textdomainPath;
    protected $textDomain;
    protected $endian;
    protected $domain='message';
    protected $locale='C';

    public static function factory()
    {
        if(!isset(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function bindTextDomain($domain, $path)
    {
        $this->textdomainPath[$domain] = $path;
        return $this;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function textDomain($domain)
    {
        $this->domain = $domain;
    }

    public function getText($message,$domain=null,$locale=null)
    {
        if($domain===null)
            $domain = $this->domain;
        if($locale===null)
            $locale = $this->locale;

        if(isset($this->textDomain[$domain][$locale])) {
            $textDomain = $this->textDomain[$domain][$locale];
        } else {
            $textDomain = $this->getTextDomain($domain,$locale);
            if($textDomain===false)
                $textDomain = array();
            $this->textDomain[$domain][$locale] = $textDomain;
        }
        if(isset($textDomain[$message]))
            return $this->textDomain[$domain][$locale][$message];
        else
            return $message;
    }

    public function getTextDomain($domain,$locale)
    {
        if(!isset($this->textdomainPath[$domain]))
            return false;
        $filename = $this->textdomainPath[$domain].'/'.$locale.'/LC_MESSAGES/'.$domain.'.mo';
        $fd = @fopen($filename, 'rb');
        if($fd===false)
            return false;
        try {
            $header = $this->readHeader($fd);
            $textDomain = $this->buildTextDomain($fd,$header);
        }
        catch(\Exception $e) {
            fclose($fd);
            throw $e;
        }
        fclose($fd);
        return $textDomain['text'];
    }

    public function readHeader($fd)
    {
        $headerStr = fread($fd,32);
        $magic = unpack('Nmagic',substr($headerStr,0,4));
        $magicStr = dechex($magic['magic']);
        if($magicStr===self::MAGIC_BIGENDIAN)
            $this->endian = 'N';
        else if($magicStr===self::MAGIC_LITTLEENDIAN)
            $this->endian = 'V';
        else
            throw new Exception\DomainException('header error.: '.dechex($magic['magic']) );
        $endian = $this->endian;
        $header = unpack("${endian}magic/${endian}version/${endian}num_string/${endian}offset_original/${endian}offset_translation/${endian}size_hash/${endian}offset_hash",$headerStr);

        return $header;
    }

    public function buildTextDomain($fd,$header)
    {
        $textDomain = array();
        $mimeHeader = '';
        $originalIndexs = $this->readStringTable($fd,$header['offset_original'],$header['num_string']);
        $translationIndexs = $this->readStringTable($fd,$header['offset_translation'],$header['num_string']);
        $headerIndex = false;
        foreach ($originalIndexs as $index => $stringIndex) {
            if($stringIndex['length']>0) {
                if(fseek($fd, $stringIndex['offset'])!=0)
                    throw new Exception\DomainException('seek error in original string.');
                $original[$index] = fread($fd,$stringIndex['length']);
            } else {
                $headerIndex = $index;
            }
        }
        foreach ($translationIndexs as $index => $stringIndex) {
            if($stringIndex['length']>0) {
                if(fseek($fd, $stringIndex['offset'])!=0)
                    throw new Exception\DomainException('seek error in original string.');
                $text = fread($fd,$stringIndex['length']);
                if($headerIndex===$index)
                    $mimeHeader = $text;
                else
                    $textDomain[$original[$index]] = $text;
            } else {
                $textDomain[$original[$index]] = '';
            }
        }
        return array('header' => $mimeHeader, 'text' => $textDomain);
    }

    protected function readStringTable($fd,$offset,$count)
    {
        if(fseek($fd, $offset)!=0)
            throw new Exception\DomainException('seek error in string table.');
        $size = 8*$count;
        $tableStr = fread($fd,$size);
        $endian = $this->endian;
        $format = "${endian}length/${endian}offset";
        for ($i=0; $i<$size; $i+=8) {
            $data = substr($tableStr,$i,8);
            $stringTable[] = unpack($format, $data);
        }
        return $stringTable;
    }

    protected function readHashTable($fd,$offset,$size)
    {
        if(fseek($fd,$offset)!=0)
            throw new Exception\DomainException('seek error in hash table.');
        return fread($fd,$size);
    }
}