<?php
namespace Aneris\Stdlib\Entity;

class EntityHydrator implements HydratorInterface
{
    public function hydrate(array $data,  $object)
    {
    	if(!($object instanceof EntityInterface))
    		throw new Exception\InvalidArgumentException('a object must be a instance of "EntityInterface"');
        return $object->hydrate($data);
    }

    public function extract($object,array $keys=null)
    {
    	if(!($object instanceof EntityInterface))
    		throw new Exception\InvalidArgumentException('a object must be a instance of "EntityInterface"');
        return $object->extract($keys);
    }
}