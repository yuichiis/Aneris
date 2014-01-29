<?php
namespace Aneris\Stdlib;

use Iterator;
use Countable;
use ArrayAccess;

class ArrayObject implements Iterator,Countable,ArrayAccess
{
	protected $elements = array();
	
 	public function offsetExists($name)
 	{
 		return isset($this->elements[$name]);
 	}

	public function offsetGet($name)
	{
		if(isset($this->elements[$name]))
			return $this->elements[$name];
		else
			return null;
	}

 	public function offsetSet($name, $value)
 	{
		$this->elements[$name] = $value;
 	}

 	public function offsetUnset($name)
 	{
        unset($this->elements[$name]);
 	}

	public function toArray()
	{
		return $this->elements;
	}

	public function isEmpty()
	{
		return empty($this->elements);
	}

	public function count()
	{
		return count($this->elements);
	}

	public function current()
	{
		return current($this->elements);
	}

	public function key()
	{
		return key($this->elements);
	}

	public function next()
	{
		next($this->elements);
	}

	public function rewind()
	{
		reset($this->elements);
	}

	public function valid()
	{
		return (key($this->elements) !== null);
	}

	public function keys()
	{
		return array_keys($this->elements);
	}
}