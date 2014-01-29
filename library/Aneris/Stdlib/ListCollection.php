<?php
namespace Aneris\Stdlib;
use Iterator;
use Countable;
use ArrayAccess;

class ListCollection implements Iterator,Countable,ArrayAccess
{
	protected $propeties;
	protected $element;

	public function add($propertyName, $element)
	{
		$this->element[] = $element;

		if(isset($this->propeties[$propertyName]))
			$this->propeties[$propertyName][] = $element;
		else
			$this->propeties[$propertyName] = array($element);
		return $this;
	}

	public function get($propertyName)
	{
		if(isset($this->propeties[$propertyName]))
			return $this->propeties[$propertyName];
		else
			return null;
	}

 	public function offsetExists($propertyName)
 	{
 		return isset($this->propeties[$propertyName]);
 	}

 	public function offsetGet($propertyName)
 	{
 		return $this->get($propertyName);
 	}

 	public function offsetSet($propertyName, $value)
 	{
 		$this->add($propertyName, $value);
 	}

 	public function offsetUnset($propertyName)
 	{
        unset($this->propeties[$propertyName]);
        $this->element = array();
        foreach($this->propeties as $list) {
        	foreach($list as $element) {
        		$this->element[] = $element;
        	}
        }
 	}

	public function toArray()
	{
		return $this->element;
	}

	public function isEmpty()
	{
		return empty($this->element);
	}

	public function count()
	{
		return count($this->element);
	}

	public function current()
	{
		return current($this->element);
	}

	public function key()
	{
		return key($this->element);
	}

	public function next()
	{
		next($this->element);
	}

	public function rewind()
	{
		reset($this->element);
	}

	public function valid()
	{
		return (key($this->element) !== null);
	}
}