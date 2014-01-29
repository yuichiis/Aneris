<?php
namespace Aneris\Stdlib\Entity;

interface EntityInterface {
    public function hydrate(array $data);
    public function extract(array $keys=null);
}