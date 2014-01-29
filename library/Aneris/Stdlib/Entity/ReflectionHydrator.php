<?php
namespace Aneris\Stdlib\Entity;

use ReflectionClass;

class ReflectionHydrator implements HydratorInterface
{
    protected static $reflections = array();

    public function hydrate(array $data, $object)
    {
        $reflections = self::getReflections(get_class($object));
        foreach ($data as $key => $value) {
            if(isset($reflections[$key])) {
                $reflections[$key]->setValue($object,$value);
            }
        }
        return $object;
    }

    public function extract($object,array $keys=null)
    {
        $reflections = self::getReflections(get_class($object));
        $result = array();
        foreach ($reflections as $name => $ref) {
            $result[$name] = $ref->getValue($object);
        }
        return $result;
    }

    public static function getReflections($className)
    {
        if(isset(self::$reflections[$className]))
            return self::$reflections[$className];

        $productRef = new ReflectionClass($className);
        $propertiesRef = $productRef->getProperties();
        foreach ($propertiesRef as $propertyRef) {
            $propertyRef->setAccessible(true);
            self::$reflections[$className][$propertyRef->getName()] = $propertyRef;
        }
        return self::$reflections[$className];
    }
}
