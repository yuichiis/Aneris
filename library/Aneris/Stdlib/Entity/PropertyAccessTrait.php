<?php
namespace Aneris\Stdlib\Entity;

trait PropertyAccessTrait
{
    public function __set($name,$value)
    {
        throw new Exception\DomainException('Invalid proparty "'.$name.'" in '.get_class($this));
    }

    public function __get($name)
    {
        throw new Exception\DomainException('Invalid proparty "'.$name.'" in '.get_class($this));
    }
}