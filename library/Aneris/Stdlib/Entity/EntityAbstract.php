<?php
namespace Aneris\Stdlib\Entity;

abstract class EntityAbstract implements EntityInterface
{
    public function __call($name, $arguments)
    {
        if(strlen($name)>3) {
            if(substr($name, 0, 3) == 'get') {
                $property = lcfirst(substr($name, 3));
                if(property_exists($this, $property)) {
                    return $this->$property;
                }
                throw new Exception\DomainException('a property is not found:'.$property);
            } else if(substr($name, 0, 3) == 'set') {
                $property = lcfirst(substr($name, 3));
                if(property_exists($this, $property)) {
                    if(!method_exists($this, 'get'.ucfirst($property))) {
                        $this->$property = $arguments[0];
                        return $this;
                    }
                    throw new Exception\DomainException('a property is read only:'.$property);
                }
                throw new Exception\DomainException('a property is not found:'.$property);
            }
        }
        throw new Exception\DomainException('a method is not found:'.$name);
    }

    public function hydrate(array $data)
    {
        $properties = get_object_vars($this);
        foreach ($data as $key => $value) {
            if(array_key_exists($key,$properties)) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    public function extract(array $keys=null)
    {
        if($keys==null)
            return get_object_vars($this);
        $result = array();
        $properties = get_object_vars($this);
        foreach ($keys as $key) {
            if(array_key_exists($key,$properties)) {
                $result[$key] = $this->$key;
            }
        }
        return $result;
    }
}