<?php
namespace Aneris\Stdlib\Entity;

interface HydratorInterface
{
    public function hydrate(array $data, $object);
    public function extract($object, array $keys=null);
}
