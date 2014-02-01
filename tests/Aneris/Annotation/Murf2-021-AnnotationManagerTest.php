<?php
namespace AnerisTest\AnnotationManagerTest;

use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Stdlib\Entity\EntityAbstract;
use ReflectionClass;
use Doctrine\Common\Annotations\SimpleAnnotationReader as DoctrineSimpleAnnotationReader;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Doctrine\ORM\Tools\Setup as DoctrineSetup;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as DoctrineAnnotationDriver;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Aneris\Form\Element\Form;

// Test Target Classes
use Aneris\Annotation\AnnotationManager;
use Aneris\Annotation\ElementType;
use Aneris\Annotation\Annotations\TargetProvider;
use Aneris\Annotation\NameSpaceExtractor;
use Aneris\Annotation\DoctrinePortability;

/**
 * @Annotation
 * @Target(FIELD,ANNOTATION_TYPE)
 */
class Test
{
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 * @Test(1)
 */
class Test2
{
    public $arg1;
    public $arg2;
}
/**
 * @Target(TYPE)
 */
class Test3
{
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 */
class Test4
{
    /**
     * @Enum({"ABC","XYZ"})
     */
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 */
class Test5
{
    /**
     * @Enum({"LARGE","SMALL"})
     */
    public $type;
}
/**
 * @Annotation
 * @Target(XYZ)
 */
class Test6
{
    public $value;
}
/**
 * @Annotation
 * @Target
 */
class Test7
{
    public $value;
}
/**
 * @Annotation
 * @Target()
 */
class Test8
{
    public $value;
}
/**
 * @Annotation
 */
class Test9
{
    /**
     * @Target()
     */
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 */
class Test10
{
    /**
     * @Enum
     */
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 */
class Test11
{
    /**
     * @Enum()
     */
    public $value;
}
/**
 * @Annotation
 * @Target(FIELD)
 * @Enum({"ABC","XYZ"})
 */
class Test12
{
    public $value;
}
/**
 * @Test3
 */
class TestEntityIncludeNotAnnotation {
    public $id;
}
class Product extends EntityAbstract
{
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
class Product2 extends EntityAbstract
{
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

class AnnotationManagerTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/annotation');
    }

    public static function tearDownAfterClass()
    {
        CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/cache/annotation');
    }

    public function setUp()
    {
    }

    public function testAnnotationAndTargetTag()
    {
        $docComment = <<<EOD
/**
 * @Test("ABC")
 * @Test2(arg1="DEF",arg2="GHI")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals(2,count($annotations));
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',get_class($annotations[0]));
        $this->assertEquals("ABC",$annotations[0]->value);

        $metaData = $reader->getMetaData('AnerisTest\AnnotationManagerTest\Test');
        $this->assertEquals(5,count(get_object_vars($metaData)));
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',$metaData->className);
        $this->assertEquals('Aneris\Annotation\Annotations\Annotation',get_class($metaData->classAnnotations[0]));
        $this->assertEquals('Aneris\Annotation\Annotations\Target',get_class($metaData->classAnnotations[1]));
        $this->assertEquals(
            (TargetProvider::TARGET_FIELD|
             TargetProvider::TARGET_ANNOTATION_TYPE),
            $metaData->classAnnotations[1]->binValue);
        $this->assertFalse($metaData->hasConstructor);
        $this->assertEquals(array('FIELD','ANNOTATION_TYPE'),$metaData->classAnnotations[1]->value);
        $this->assertNull($metaData->fieldAnnotations);
        $this->assertNull($metaData->methodAnnotations);

        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test2',get_class($annotations[1]));
        $this->assertEquals("DEF",$annotations[1]->arg1);
        $this->assertEquals("GHI",$annotations[1]->arg2);

        $metaData = $reader->getMetaData('AnerisTest\AnnotationManagerTest\Test2');
        $this->assertEquals(5,count(get_object_vars($metaData)));
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test2',$metaData->className);
        $this->assertEquals(
            (TargetProvider::TARGET_FIELD),
            $metaData->classAnnotations[1]->binValue);
        $this->assertFalse($metaData->hasConstructor);
        $this->assertEquals(3,count($metaData->classAnnotations));
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',get_class($metaData->classAnnotations[2]));
        $this->assertEquals(1,$metaData->classAnnotations[2]->value);
        $this->assertNull($metaData->fieldAnnotations);
        $this->assertNull($metaData->methodAnnotations);
    }

    public function testListParam()
    {
        $docComment = <<<EOD
/**
 * @Test("ABC","XYZ")
 * @Test(123,456)
 * @Test({123,456})
 * @Test({123})
 * @Test2(arg1=1,arg2=2)
 * @Test({@Test(1),@Test(2)})
 * @Test({@Test(3)})
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',get_class($annotations[0]));
        $this->assertEquals(array("ABC","XYZ"),$annotations[0]->value);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',get_class($annotations[1]));
        $this->assertEquals(array(123,456),$annotations[1]->value);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',get_class($annotations[2]));
        $this->assertEquals(array(123,456),$annotations[2]->value);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',get_class($annotations[3]));
        $this->assertEquals(array(123),$annotations[3]->value);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test2',get_class($annotations[4]));
        $this->assertEquals(1,$annotations[4]->arg1);
        $this->assertEquals(2,$annotations[4]->arg2);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',get_class($annotations[5]));
        $this->assertEquals(1,$annotations[5]->value[0]->value);
        $this->assertEquals(2,$annotations[5]->value[1]->value);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',get_class($annotations[6]));
        $this->assertEquals(3,$annotations[6]->value[0]->value);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage the class is not annotation class.: AnerisTest\AnnotationManagerTest\Test3 in Test\Test::$id: filename.php
     */
    public function testAnnotationTagError()
    {
        $docComment = <<<EOD
/**
 * @Test3("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 123,
        );

        $annotations = $parser->searchAnnotation($docComment,$location);

        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',get_class($annotations[0]));
        $this->assertEquals("ABC",$annotations[0]->value);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage the annotation "@AnerisTest\AnnotationManagerTest\Test" do not allow to TYPE in Test\Test::$id: filename.php
     */
    public function testTargetTagErrorNotAllow()
    {
        $docComment = <<<EOD
/**
 * @Test("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php',
            'linenumber' => 100);
        $annotations = $parser->searchAnnotation($docComment,$location);

        //var_dump($reader->getMetaData('AnerisTest\AnnotationManagerTest\Test'));

        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test',get_class($annotations[0]));
        $this->assertEquals("ABC",$annotations[0]->value);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage the paremeter "XYZ" is a invalid argument for the @Target in AnerisTest\AnnotationManagerTest\Test6:
     */
    public function testTargetTagErrorInvalidElementType()
    {
        $docComment = <<<EOD
/**
 * @Test6("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);

    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Target dose not have element types in AnerisTest\AnnotationManagerTest\Test7:
     */
    public function testTargetTagErrorHasNull()
    {
        $docComment = <<<EOD
/**
 * @Test7("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);

    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Target dose not have element types in AnerisTest\AnnotationManagerTest\Test8:
     */
    public function testTargetTagErrorHasEmptyArray()
    {
        $docComment = <<<EOD
/**
 * @Test8("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Target must be placed as ANNOTAION_TYPE in AnerisTest\AnnotationManagerTest\Test9::$value:
     */
    public function testTargetTagErrorInFeild()
    {
        $docComment = <<<EOD
/**
 * @Test9("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);

    }

    public function testEnumTag()
    {
        $docComment = <<<EOD
/**
 * @Test4("ABC")
 * @Test4("XYZ")
 * @Test5(type="LARGE")
 * @Test5(type="SMALL")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test4',get_class($annotations[0]));
        $this->assertEquals("ABC",$annotations[0]->value);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test4',get_class($annotations[1]));
        $this->assertEquals("XYZ",$annotations[1]->value);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test5',get_class($annotations[2]));
        $this->assertEquals("LARGE",$annotations[2]->type);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Test5',get_class($annotations[3]));
        $this->assertEquals("SMALL",$annotations[3]->type);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage a value "DEF" is not allowed for the field "value" of annotation @AnerisTest\AnnotationManagerTest\Test4 in Test\Test::$id: filename.php
     */
    public function testEnumTagErrorNotArrowToValue()
    {
        $docComment = <<<EOD
/**
 * @Test4("DEF")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage a value "DEF" is not allowed for the field "type" of annotation @AnerisTest\AnnotationManagerTest\Test5 in Test\Test::$id: filename.php
     */
    public function testEnumTagErrorNotArrowToArgument()
    {
        $docComment = <<<EOD
/**
 * @Test5(type="DEF")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Enum dose not have enumulated values in AnerisTest\AnnotationManagerTest\Test10::$value:
     */
    public function testEnumTagErrorHasNull()
    {
        $docComment = <<<EOD
/**
 * @Test10("DEF")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Enum dose not have enumulated values in AnerisTest\AnnotationManagerTest\Test11::$value:
     */
    public function testEnumTagErrorHasEmptyArray()
    {
        $docComment = <<<EOD
/**
 * @Test11("DEF")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::FIELD,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage @Enum must be placed as FILED in AnerisTest\AnnotationManagerTest\Test12:
     */
    public function testEnumTagErrorInANNOTATIONTYPE()
    {
        $docComment = <<<EOD
/**
 * @Test12("ABC")
 */
EOD;
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $parser = $reader->getParser();
        $location = array(
            'target' => ElementType::TYPE,
            'class'  => 'Test\\Test',
            'name'   => 'id',
            'uri'    => 'Test\\Test::$id',
            'filename' => 'filename.php');
        $annotations = $parser->searchAnnotation($docComment,$location);

    }

    public function testFromClass()
    {
        $reader = new AnnotationManager();
        $reader->addNameSpace('Doctrine\ORM\Mapping');
        $reader->addNameSpace('Aneris\Validator\Constraints');
        $classRef = new ReflectionClass('AnerisTest\AnnotationManagerTest\Product2');

        $annotations['__CLASS__'] = $reader->getClassAnnotations($classRef);
        $this->assertEquals(1,count($annotations['__CLASS__']));
        $this->assertEquals('Aneris\Form\Element\Form',get_class($annotations['__CLASS__'][0]));
        $propertyRefs = $classRef->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $annotations[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($annotations['id']));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['id'][0]));
        $this->assertEquals('Doctrine\ORM\Mapping\GeneratedValue',get_class($annotations['id'][1]));
        $this->assertEquals(3,count($annotations['id2']));
        $this->assertEquals('Doctrine\ORM\Mapping\Column',get_class($annotations['id2'][0]));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['id2'][1]));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['id2'][2]));
        $this->assertEquals(2,count($annotations['stock']));
        $this->assertEquals('Doctrine\ORM\Mapping\Column',get_class($annotations['stock'][0]));
        $this->assertEquals('Aneris\Validator\Constraints\CList',get_class($annotations['stock'][1]));
        $this->assertEquals(2,count($annotations['stock'][1]->value));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['stock'][1]->value[0]));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['stock'][1]->value[1]));
    }

    /**
     * @requires PHP 5.4.0
     */
    public function testFromClassWithTrait()
    {
        require_once ANERIS_TEST_RESOURCES.'/AcmeTest/Annotation/Entity/class_with_trait.php';
        $reader = new AnnotationManager();
        $reader->addNameSpace('Doctrine\ORM\Mapping');
        $reader->addNameSpace('Aneris\Validator\Constraints');
        $classRef = new ReflectionClass('AcmeTest\Annotation\Entity\Product2WithTrait');

        $annotations['__CLASS__'] = $reader->getClassAnnotations($classRef);
        $this->assertEquals(1,count($annotations['__CLASS__']));
        $this->assertEquals('Aneris\Form\Element\Form',get_class($annotations['__CLASS__'][0]));
        $propertyRefs = $classRef->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $annotations[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($annotations['id']));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['id'][0]));
        $this->assertEquals('Doctrine\ORM\Mapping\GeneratedValue',get_class($annotations['id'][1]));
        $this->assertEquals(3,count($annotations['id2']));
        $this->assertEquals('Doctrine\ORM\Mapping\Column',get_class($annotations['id2'][0]));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['id2'][1]));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['id2'][2]));
        $this->assertEquals(2,count($annotations['stock']));
        $this->assertEquals('Doctrine\ORM\Mapping\Column',get_class($annotations['stock'][0]));
        $this->assertEquals('Aneris\Validator\Constraints\CList',get_class($annotations['stock'][1]));
        $this->assertEquals(2,count($annotations['stock'][1]->value));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['stock'][1]->value[0]));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['stock'][1]->value[1]));
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage the class is not annotation class.: AnerisTest\AnnotationManagerTest\Test3 in AnerisTest\AnnotationManagerTest\TestEntityIncludeNotAnnotation:
     */
    public function testAnnotationTagErrorIncluding()
    {
        $reader = new AnnotationManager();
        $reader->addNameSpace('AnerisTest\AnnotationManagerTest');
        $classRef = new ReflectionClass('AnerisTest\AnnotationManagerTest\TestEntityIncludeNotAnnotation');
        $annotations = $reader->getClassAnnotations($classRef);
    }

    /**
     * @requires PHP 5.4.0
     */
    public function testNamespaceExtractor()
    {
        $parser = new NameSpaceExtractor(ANERIS_TEST_RESOURCES.'/annotation/namespace.php');
        $imports = $parser->getAllImports();
        $this->assertEquals(3,count($imports));
        $this->assertEquals(1,count($imports['AnerisTest\AnnotationManagerTest\Foo']));
        $this->assertEquals(1,count($imports['AnerisTest\AnnotationManagerTest\Bar']));
        $this->assertEquals(2,count($imports['__TOPLEVEL__']));
        $this->assertEquals('Aneris\Stdlib\ListCollection',$imports['AnerisTest\AnnotationManagerTest\Foo']['ListCollection']);
        $this->assertEquals('Aneris\Stdlib\PriorityQueue',$imports['AnerisTest\AnnotationManagerTest\Bar']['ListCollection']);
        $this->assertEquals('stdClass',$imports['__TOPLEVEL__']['ListCollection']);
        $this->assertEquals('Aneris\TestList',$imports['__TOPLEVEL__']['TestList']);
        $classes = $parser->getAllClass();
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Foo\MyClass',$classes[0]);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Foo\MyClass2',$classes[1]);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\Bar\MyClass',$classes[2]);
    }

    public function testNamespaceExtractorWithoutTrait()
    {
        $parser = new NameSpaceExtractor(ANERIS_TEST_RESOURCES.'/annotation/namespace_without_trait.php');
        $imports = $parser->getAllImports();
        $this->assertEquals(3,count($imports));
        $this->assertEquals(1,count($imports['AnerisTest\AnnotationManagerTest\FooWithoutTrait']));
        $this->assertEquals(1,count($imports['AnerisTest\AnnotationManagerTest\BarWithoutTrait']));
        $this->assertEquals(2,count($imports['__TOPLEVEL__']));
        $this->assertEquals('Aneris\Stdlib\ListCollection',$imports['AnerisTest\AnnotationManagerTest\FooWithoutTrait']['ListCollection']);
        $this->assertEquals('Aneris\Stdlib\PriorityQueue',$imports['AnerisTest\AnnotationManagerTest\BarWithoutTrait']['ListCollection']);
        $this->assertEquals('stdClass',$imports['__TOPLEVEL__']['ListCollection']);
        $this->assertEquals('Aneris\TestList',$imports['__TOPLEVEL__']['TestList']);
        $classes = $parser->getAllClass();
        $this->assertEquals('AnerisTest\AnnotationManagerTest\FooWithoutTrait\MyClass',$classes[0]);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\FooWithoutTrait\MyClass2',$classes[1]);
        $this->assertEquals('AnerisTest\AnnotationManagerTest\BarWithoutTrait\MyClass',$classes[2]);
    }

    public function testImportsAbsolute()
    {
        $reader = new AnnotationManager();
        $ref = new ReflectionClass('AcmeTest\Annotation\Entity\Entity1');
        $nameSpace = $ref->getNamespaceName();
        $className = $ref->name;
        $fileName  = $ref->getFileName();
        $location['class'] = $className;
        $reader->addImports($nameSpace,$className,$fileName);
        $this->assertEquals('AcmeTest\Annotation\Mapping\Entity',$reader->resolvAnnotationClass('Entity',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Table',$reader->resolvAnnotationClass('Table',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Id',$reader->resolvAnnotationClass('Id',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\GeneratedValue',$reader->resolvAnnotationClass('GeneratedValue',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',$reader->resolvAnnotationClass('Column',$location));

        $annos = $reader->getClassAnnotations($ref);
        $this->assertEquals(2,count($annos));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Entity',get_class($annos[0]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Table',get_class($annos[1]));

        $propertyRefs = $ref->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $fields[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(3,count($fields));
        $this->assertEquals(3,count($fields['id']));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Id',get_class($fields['id'][0]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',get_class($fields['id'][1]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\GeneratedValue',get_class($fields['id'][2]));
        $this->assertEquals(1,count($fields['name']));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',get_class($fields['name'][0]));

        // import current namespace at @nest1
        $this->assertEquals(1,count($fields['nest']));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Nest1',get_class($fields['nest'][0]));
        $metaData = $reader->getMetaData('AcmeTest\Annotation\Mapping\Nest1');
        $this->assertEquals(3,count($metaData->classAnnotations));
        $this->assertEquals('Aneris\Annotation\Annotations\Annotation',get_class($metaData->classAnnotations[0]));
        $this->assertEquals('Aneris\Annotation\Annotations\Target',get_class($metaData->classAnnotations[1]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Nest2',get_class($metaData->classAnnotations[2]));
    }

    public function testImportsNameSpace()
    {
        $reader = new AnnotationManager();
        $ref = new ReflectionClass('AcmeTest\Annotation\Entity\Entity2');
        $nameSpace = $ref->getNamespaceName();
        $className = $ref->name;
        $fileName  = $ref->getFileName();
        $location['class'] = $className;
        $reader->addImports($nameSpace,$className,$fileName);
        $this->assertEquals('AcmeTest\Annotation\Mapping\Entity',$reader->resolvAnnotationClass('Annotation\Mapping\Entity',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Table',$reader->resolvAnnotationClass('Annotation\Mapping\Table',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Id',$reader->resolvAnnotationClass('Annotation\Mapping\Id',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\GeneratedValue',$reader->resolvAnnotationClass('Annotation\Mapping\GeneratedValue',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',$reader->resolvAnnotationClass('Annotation\Mapping\Column',$location));

        $annos = $reader->getClassAnnotations($ref);
        $this->assertEquals(2,count($annos));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Entity',get_class($annos[0]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Table',get_class($annos[1]));

        $propertyRefs = $ref->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $fields[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($fields));
        $this->assertEquals(3,count($fields['id']));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Id',get_class($fields['id'][0]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',get_class($fields['id'][1]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\GeneratedValue',get_class($fields['id'][2]));
        $this->assertEquals(1,count($fields['name']));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',get_class($fields['name'][0]));
    }

    public function testImportsAlias()
    {
        $reader = new AnnotationManager();
        $ref = new ReflectionClass('AcmeTest\Annotation\Entity\Entity3');
        $nameSpace = $ref->getNamespaceName();
        $className = $ref->name;
        $fileName  = $ref->getFileName();
        $location['class'] = $className;
        $reader->addImports($nameSpace,$className,$fileName);
        $this->assertEquals('AcmeTest\Annotation\Mapping\Entity',$reader->resolvAnnotationClass('ORM\Entity',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Table',$reader->resolvAnnotationClass('ORM\Table',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Id',$reader->resolvAnnotationClass('ORM\Id',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\GeneratedValue',$reader->resolvAnnotationClass('ORM\GeneratedValue',$location));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',$reader->resolvAnnotationClass('ORM\Column',$location));

        $annos = $reader->getClassAnnotations($ref);
        $this->assertEquals(2,count($annos));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Entity',get_class($annos[0]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Table',get_class($annos[1]));

        $propertyRefs = $ref->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $fields[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($fields));
        $this->assertEquals(3,count($fields['id']));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Id',get_class($fields['id'][0]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',get_class($fields['id'][1]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\GeneratedValue',get_class($fields['id'][2]));
        $this->assertEquals(1,count($fields['name']));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',get_class($fields['name'][0]));
    }

    public function testDoctrineParserSimple()
    {
        DoctrinePortability::patch();
        
        $reader = new DoctrineSimpleAnnotationReader();
        $reader->addNameSpace('Doctrine\ORM\Mapping');
        $reader->addNameSpace('Aneris\Validator\Constraints');
        $reader->addNameSpace('Aneris\Form\Element');
        $classRef = new ReflectionClass('AnerisTest\AnnotationManagerTest\Product2');
        //echo $classRef->getDocComment();

        $annotations['__CLASS__'] = $reader->getClassAnnotations($classRef);
        $this->assertEquals(1,count($annotations['__CLASS__']));
        $this->assertEquals('Aneris\Form\Element\Form',get_class($annotations['__CLASS__'][0]));
        $propertyRefs = $classRef->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $annotations[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($annotations['id']));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['id'][0]));
        $this->assertEquals('Doctrine\ORM\Mapping\GeneratedValue',get_class($annotations['id'][1]));
        $this->assertEquals(3,count($annotations['id2']));
        $this->assertEquals('Doctrine\ORM\Mapping\Column',get_class($annotations['id2'][0]));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['id2'][1]));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['id2'][2]));
        $this->assertEquals(2,count($annotations['stock']));
        $this->assertEquals('Doctrine\ORM\Mapping\Column',get_class($annotations['stock'][0]));
        $this->assertEquals('Aneris\Validator\Constraints\CList',get_class($annotations['stock'][1]));
        $this->assertEquals(2,count($annotations['stock'][1]->value));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['stock'][1]->value[0]));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($annotations['stock'][1]->value[1]));
    }


    public function testDoctrineParserImportsAlias()
    {
        DoctrinePortability::patch();

        $reader = new DoctrineAnnotationReader();
        $ref = new ReflectionClass('AcmeTest\Annotation\Entity\Entity3');

        $annos = $reader->getClassAnnotations($ref);
        $this->assertEquals(2,count($annos));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Entity',get_class($annos[0]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Table',get_class($annos[1]));

        $propertyRefs = $ref->getProperties();
        foreach($propertyRefs as $propertyRef) {
            $fields[$propertyRef->getName()] = $reader->getPropertyAnnotations($propertyRef);
        }
        $this->assertEquals(2,count($fields));
        $this->assertEquals(3,count($fields['id']));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Id',get_class($fields['id'][0]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',get_class($fields['id'][1]));
        $this->assertEquals('AcmeTest\Annotation\Mapping\GeneratedValue',get_class($fields['id'][2]));
        $this->assertEquals(1,count($fields['name']));
        $this->assertEquals('AcmeTest\Annotation\Mapping\Column',get_class($fields['name'][0]));
    }

    public function testAnnotationOnDoctrine()
    {
        $paths = array(
            ANERIS_TEST_RESOURCES.'/AcmeTest/Doctrine1/Entity',
        );
        $isDevMode = false;
        $connection = array(
            'driver' => 'pdo_sqlite',
            'path' => ANERIS_TEST_DATA . '/db.sqlite',
        );
        $setup = DoctrineSetup::createConfiguration($isDevMode);

        $reader = new AnnotationManager();
        $reader->addNameSpace('Doctrine\ORM\Mapping');      
        $annotationDriver = new DoctrineAnnotationDriver($reader, (array) $paths);
        $setup->setMetadataDriverImpl($annotationDriver);

        $entityManager = DoctrineEntityManager::create($connection, $setup);
        $product2 = $entityManager->find('AcmeTest\Doctrine1\Entity\Product',1);
    }       
}
