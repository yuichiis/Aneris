<?php
namespace Aneris\Stdlib\Entity;

class SetterHydrator implements HydratorInterface
{
    public function hydrate(array $data, $object)
    {
        foreach ($data as $key => $value) {
            if(property_exists($object, $key)) {
                $setter = 'set'.ucfirst($key);
                if(is_callable(array($object,$setter)))
                    $object->$setter($value);
            }
        }
        return $object;
    }

    public function extract($object,array $keys=null)
    {
        if($keys==null) {
            throw new Exception\InvalidArgumentException('need keys to extract for "SetterHydrator"');
        }
        $result = array();
        foreach ($keys as $key) {
            if(property_exists($object, $key)) {
                $getter = 'get'.ucfirst($key);
                if(is_callable(array($object,$getter)))
                    $result[$key] = $object->$getter();
            }
        }
        return $result;
    }
}