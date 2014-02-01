<?php
namespace AcmeTest\Annotation\Entity;

use Aneris\Form\Element\Form;

use Aneris\Stdlib\Entity\EntityInterface;
use Aneris\Stdlib\Entity\EntityTrait;

class ProductWithTrait implements EntityInterface
{
    use EntityTrait;
    /** @Max(10) @GeneratedValue **/
    protected $id;
    /** @Min(10) @Column **/
    protected $id2;
    /** @Max(100) @Column(name="stock_value")**/
    protected $stock;
}
/**
* @Form(attributes={"method"="POST"})
*/
class Product2WithTrait implements EntityInterface
{
    use EntityTrait;
    /**
    * @Max(value=10) @GeneratedValue 
    */
    public $id;
    /**
     * @Column
     * #@Max.List({
     *    @Max(value=20,groups={"a"}) 
     *    @Max(value=30,groups={"c"})
     * #})
     */
    public $id2;
    /**
     * @Column
     * @CList({
     *    @Max(value=20,groups={"a"}),
     *    @Max(value=30,groups={"c"})
     * })
     */
    public $stock;
}
