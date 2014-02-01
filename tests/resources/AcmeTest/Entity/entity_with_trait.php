<?php
namespace AcmeTest\Entity;

use Aneris\Stdlib\Entity\EntityTrait;
use Aneris\Stdlib\Entity\EntityInterface;

class Bean2 implements EntityInterface
{
    use EntityTrait;

    protected $id;
    protected $name;
    private   $privateVar;
    public function getId()
    {
        return $this->id;
    }
    // a getter is not defined for name
}
