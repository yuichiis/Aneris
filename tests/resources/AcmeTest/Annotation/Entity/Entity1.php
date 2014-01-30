<?php
namespace AcmeTest\Annotation\Entity;

use AcmeTest\Annotation\Mapping\Entity;
use AcmeTest\Annotation\Mapping\Table;
use AcmeTest\Annotation\Mapping\Id;
use AcmeTest\Annotation\Mapping\GeneratedValue;
use AcmeTest\Annotation\Mapping\Column;
use AcmeTest\Annotation\Mapping\Nest1;

/**
 * @Entity @Table(name="products")
 **/
class Entity1
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;
    /** @Column(type="string") **/
    protected $name;

    /** @Nest1 **/
    protected $nest;
}