<?php
namespace AnerisTest\HydratorTest;

use stdClass;

// Test Target Classes
use Aneris\Stdlib\Entity\EntityTrait;
use Aneris\Stdlib\Entity\EntityAbstract;
use Aneris\Stdlib\Entity\EntityInterface;
use Aneris\Stdlib\Entity\EntityHydrator;
use Aneris\Stdlib\Entity\ReflectionHydrator;
use Aneris\Stdlib\Entity\PropertyHydrator;

class Product
{
    protected $id;
    protected $name;
    private   $privateVar;

    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getPrivateVar()
    {
        return $this->privateVar;
    }
}

class Bean1 extends EntityAbstract
{
    protected $id;
    protected $name;
    private   $privateVar;

    public function getId()
    {
        return $this->id;
    }
    // a getter is not defined for name
}

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

class Object1 
{
    protected $id;
    public    $name;
    private   $privateVar;

    public function getId()
    {
        return $this->id;
    }
    // a getter is not defined for name
}

class Aneris2HydratorTest extends \PHPUnit_Framework_TestCase
{
    public function testHydratorReflection()
    {

        $std = new stdClass();
        $std->id = 1;
        $std->name = 'abc';
        $std->privateVar = 'def';
        $array = get_object_vars($std);

        $product = new Product();

        $hydrator = new ReflectionHydrator();

        $hydrator->hydrate($array,$product);
        $this->assertEquals($array['id'], $product->getId());
        $this->assertEquals($array['name'], $product->getName());
        $this->assertEquals($array['privateVar'], $product->getPrivateVar());

        $std2 = $hydrator->extract($product);
        $this->assertEquals($std2['id'], $product->getId());
        $this->assertEquals($std2['name'], $product->getName());
        $this->assertEquals($std2['privateVar'], $product->getPrivateVar());
    }

    public function testHydratorBean()
    {
        $std = new stdClass();
        $std->id = 1;
        $std->name = 'abc';
        $std->privateVar = 'def';
        $array = get_object_vars($std);

        $bean1 = new Bean1();

        $bean1->hydrate($array);
        $this->assertEquals($array['id'], $bean1->getId());
        $this->assertEquals($array['name'], $bean1->getName());
        //$this->assertEquals($array['privateVar'], $bean1->getPrivateVar());

        $std2 = $bean1->extract();
        $this->assertEquals($std2['id'], $bean1->getId());
        $this->assertEquals($std2['name'], $bean1->getName());

        $bean1->setName('xyz');
        $this->assertEquals('xyz', $bean1->getName());

        $std3 = $bean1->extract();
        $this->assertEquals('xyz',$std3['name']);
    }

    /**
     * @expectedException        Aneris\Stdlib\Entity\Exception\DomainException
     * @expectedExceptionMessage a property is not found:abc
     */
    public function testHydratorBeanNotFoundAccessViolationToRead()
    {
        $bean1 = new Bean1();
        $bean1->getAbc();
    }

    /**
     * @expectedException        Aneris\Stdlib\Entity\Exception\DomainException
     * @expectedExceptionMessage a property is not found:abc
     */
    public function testHydratorBeanNotFoundAccessViolationToWrite()
    {
        $bean1 = new Bean1();
        $bean1->setAbc();
    }

    /**
     * @expectedException        Aneris\Stdlib\Entity\Exception\DomainException
     * @expectedExceptionMessage a property is read only:id
     */
    public function testHydratorBeanPrivateAccessViolation()
    {
        $bean1 = new Bean1();
        $bean1->setId(100);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function PassForErrortestHydratorBeanReadOnlyAccessViolation()
    {
        // PHPUnit can not avoid the error
        $bean1 = new Bean1();
        $bean1->getPrivateVar();
    }

    public function testHydratorBeanTrait()
    {
        $std = new stdClass();
        $std->id = 1;
        $std->name = 'abc';
        $std->privateVar = 'def';
        $array = get_object_vars($std);

        $bean1 = new Bean2();

        $bean1->hydrate($array);
        $this->assertEquals($array['id'], $bean1->getId());
        $this->assertEquals($array['name'], $bean1->getName());
        $this->assertEquals($array['privateVar'], $bean1->getPrivateVar());

        $std2 = $bean1->extract();
        $this->assertEquals($std2['id'], $bean1->getId());
        $this->assertEquals($std2['name'], $bean1->getName());
        $this->assertEquals($std2['privateVar'], $bean1->getPrivateVar());

        $bean1->setName('xyz');
        $this->assertEquals('xyz', $bean1->getName());
        $bean1->setPrivateVar('opq');
        $this->assertEquals('opq', $bean1->getPrivateVar());

        $std3 = $bean1->extract();
        $this->assertEquals('xyz',$std3['name']);
        $this->assertEquals('opq',$std3['privateVar']);
    }

    /**
     * @expectedException        Aneris\Stdlib\Entity\Exception\DomainException
     * @expectedExceptionMessage a property is not found:abc
     */
    public function testHydratorBeanTraitNotFoundAccessViolationToRead()
    {
        $bean1 = new Bean2();
        $bean1->getAbc();
    }

    /**
     * @expectedException        Aneris\Stdlib\Entity\Exception\DomainException
     * @expectedExceptionMessage a property is not found:abc
     */
    public function testHydratorBeanTraitNotFoundAccessViolationToWrite()
    {
        $bean1 = new Bean2();
        $bean1->setAbc();
    }

    /**
     * @expectedException        Aneris\Stdlib\Entity\Exception\DomainException
     * @expectedExceptionMessage a property is read only:id
     */
    public function testHydratorBeanTraitReadOnlyAccessViolation()
    {
        $bean1 = new Bean2();
        $bean1->setId(100);
    }

    public function testHydratorEntityHydrator()
    {
        // Same as ReflectionHydrator

        $std = new stdClass();
        $std->id = 1;
        $std->name = 'abc';
        $array = get_object_vars($std);

        $product = new Bean1();

        $hydrator = new EntityHydrator();

        $hydrator->hydrate($array,$product);
        $this->assertEquals($array['id'], $product->getId());
        $this->assertEquals($array['name'], $product->getName());

        $std2 = $hydrator->extract($product);
        $this->assertEquals($std2['id'], $product->getId());
        $this->assertEquals($std2['name'], $product->getName());
    }

    public function testHydratorPropertyHydrator()
    {
        $std = new stdClass();
        $std->id = 1;
        $std->name = 'abc';
        $array = get_object_vars($std);

        $product = new Object1();

        $hydrator = new PropertyHydrator();

        $hydrator->hydrate($array,$product);
        $this->assertNull($product->getId());
        $this->assertEquals($array['name'], $product->name);

        $std2 = $hydrator->extract($product);
        $this->assertFalse(array_key_exists('id', $std2));
        $this->assertEquals($std2['name'], $product->name);
    }
}
