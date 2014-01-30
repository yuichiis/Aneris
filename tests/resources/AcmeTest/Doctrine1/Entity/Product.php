<?php
namespace AcmeTest\Doctrine1\Entity;

use Doctrine\ORM\Mapping as ORM;

//use Doctrine\ORM\Mapping\Entity;
//use Doctrine\ORM\Mapping\Table;
//use Doctrine\ORM\Mapping\Id;
//use Doctrine\ORM\Mapping\Column;
//use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @ORM\Entity @ORM\Table(name="products")
 **/
class Product
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue **/
    protected $id;
    /** @ORM\Column(type="string") **/
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}