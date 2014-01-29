<?php
namespace Aneris\Stdlib\Entity;

abstract class PropertyAccessAbstract implements PropertyAccessPolicyInterface
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