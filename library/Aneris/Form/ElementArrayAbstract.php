<?php
namespace Aneris\Form;

use Aneris\Stdlib\ArrayObject;

abstract class ElementArrayAbstract extends ArrayObject
{
	public function __clone()
	{
		$newElements = array();
		foreach ($this->elements as $key => $value) {
			$newElements[$key] = clone $value;
		}
		$this->elements = $newElements;
	}

 	public function offsetSet($name, $value)
 	{
 		if(!($value instanceof ElementInterface))
	        throw new Exception\DomainException('Must be a Element class to set into the offset "'.$name.'" in '.get_class($this));
		$this->elements[$name] = $value;
 	}

    public function __set($name,$value)
    {
        throw new Exception\DomainException('Invalid proparty "'.$name.'" in '.get_class($this));
    }

    public function __get($name)
    {
        throw new Exception\DomainException('Invalid proparty "'.$name.'" in '.get_class($this));
    }
}