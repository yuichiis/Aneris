<?php
namespace Aneris\Stdlib\Entity;

class PropertyHydrator implements HydratorInterface
{
    public function hydrate(array $data, $object)
    {
        $properties = get_object_vars($object);
        foreach ($data as $key => $value) {
            if(array_key_exists($key,$properties)) {
                $object->$key = $value;
            }
        }
        return $object;
    }

    public function extract($object,array $keys=null)
    {
        if($keys==null)
            return get_object_vars($object);
        $result = array();
        $properties = get_object_vars($object);
        foreach ($keys as $key) {
            if(array_key_exists($key,$properties)) {
                $result[$key] = $object->$key;
            }
        }
        return $result;
    }
}