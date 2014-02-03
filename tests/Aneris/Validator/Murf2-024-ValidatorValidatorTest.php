<?php
namespace AnerisTest\ValidatorValidatorTest;

use Aneris\Stdlib\Entity\EntityAbstract;
use Aneris\Stdlib\Entity\PropertyAccessPolicyInterface;
use Aneris\Container\ModuleManager;
use Aneris\Stdlib\I18n\Translator;
use Aneris\Annotation\DoctrinePortability;
use Aneris\Module\Doctrine\AnnotationReaderProxy;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Symfony\Component\Validator\Constraints as SymfonyAssert;
use Symfony\Component\Validator\ValidatorBuilder as SymfonyValidatorBuilder;
use Zend\I18n\Translator\Translator as ZendTranslator;

// Test Target Classes
use Aneris\Validator\ConstraintAbstract;
use Aneris\Validator\ConstraintInterface;
use Aneris\Validator\ConstraintValidatorInterface;
use Aneris\Validator\ConstraintValidatorContextInterface;
use Aneris\Validator\Validator;

use Aneris\Validator\Constraints\AssertTrue;
use Aneris\Validator\Constraints\CList;
use Aneris\Validator\Constraints\Constraint;
use Aneris\Validator\Constraints\Max;
use Aneris\Validator\Constraints\Min;
use Aneris\Validator\Constraints\NotNull;
use Aneris\Validator\Constraints\Size;
use Aneris\Validator\Constraints\GroupSequence;

class Product extends EntityAbstract
{
    /** @Max(10) **/
    protected $id;
    /** @Min(10) **/
    protected $id2;
    /** @Max(100) **/
    protected $stock;
}

class Product2 extends EntityAbstract
{
    /** @Min(10) **/
    protected $id;
    /** @Max(value=100, message="stock max is {value}.") **/
    protected $stock;
}

class Product3 extends EntityAbstract
{
    /** @Max(10) **/
    protected $id;
    /** @Size(max=10, message="name max length is {max}.") **/
    protected $name;
    /** @Size(min=5, message="description min length is {min}.") **/
    protected $description;
    /** @Size(min=5, max=10, message="code length is {min} - {max}.") **/
    protected $code;
}

class Product4 extends EntityAbstract
{
    /** @Max(10) @NotNull **/
    protected $id;
    /** @Max(100)  @NotNull **/
    protected $stock;
}

class Product5 extends EntityAbstract
{
    /** @Max(10) **/
    protected $id;
    /** @Size(min=8) **/
    protected $password;
    /** @Size(min=8) **/
    protected $passwordAgain;
    /** @AssertTrue(message="Passwords do not match.",path="password") **/
    public function comparePassword()
    {
        return ($this->password == $this->passwordAgain);
    }
}

class Product6 implements PropertyAccessPolicyInterface
{
    /** @Max(10) **/
    public $id;
    /** @Max(100) **/
    public $stock;
}

class Product7 extends EntityAbstract
{
    /** @Max(value=10) **/
    public $id;
    /**
     * #@Max.List({
     *    @Max(value=20,groups={"a"}) 
     *    @Max(value=30,groups={"c"})
     * #})
     */
    public $id2;
    /** @Max(value=100,groups={"Default","a"})  @Min(value=110,groups={"b"}) **/
    public $stock;
}

class Product8 extends EntityAbstract
{
    /** @Max(10) **/
    protected $id;
    /**
     * @CList({
     *    @Max(20),
     *    @Max(value=10,groups={"c"})
     * })
     */
    public $id2;
}

class Product9 extends EntityAbstract
{
    /** @Test **/
    protected $id;
    /**
     * @CList({
     *    @Max(20),
     *    @Test(groups={"c"})
     * })
     */
    public $id2;
    /** @Test2 **/
    protected $id3;
}

/**
 * @GroupSequence({"a","b","c"})
 */
class Product10 extends EntityAbstract
{
    /** @Max(value=10,groups={"c"}) **/
    protected $id;
    /** @Max(value=10,groups={"b"}) **/
    protected $id2;
    /** @Max(value=10,groups={"a"}) **/
    protected $id3;
}

/**
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE }) #, CONSTRUCTOR, PARAMETER
 * @Constraint(validatedBy = {})
 * @Max(10)
 */
class Test extends ConstraintAbstract
{
    public $message = "must be false.";
    public $groups = array();
    public $payload = array();
}

class TestValidator implements ConstraintValidatorInterface
{
    public function initialize(ConstraintInterface $constraint)
    {
    }

    public function isValid($value, ConstraintValidatorContextInterface $context)
    {
        return true;
    }
}

/**
 * @Annotation
 * @Target({ METHOD, FIELD, ANNOTATION_TYPE }) #, CONSTRUCTOR, PARAMETER
 * @Constraint(validatedBy = {})
 * @Test
 */
class Test2 extends ConstraintAbstract
{
    public $message = "must be false.";
    public $groups = array();
    public $payload = array();
}

class Test2Validator implements ConstraintValidatorInterface
{
    public function initialize(ConstraintInterface $constraint)
    {
    }

    public function isValid($value, ConstraintValidatorContextInterface $context)
    {
        return true;
    }
}

class ProductSymfony extends EntityAbstract
{
    /** @SymfonyAssert\LessThanOrEqual(10) **/
    protected $id;
    /** @SymfonyAssert\LessThanOrEqual(10) **/
    protected $id2;
    /** @SymfonyAssert\LessThanOrEqual(100) **/
    protected $stock;
}

class ValidatorValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function encodeConsoleCode($text)
    {
        switch(PHP_OS) {
            case 'WIN32':
            case 'WINNT':
                $code = "SJIS";
                break;
             
            default:
                $code = "JIS";
                break;
         } 
        return mb_convert_encoding($text, $code, "auto");
    }

    public function testCombination()
    {
        $validator = new Validator();

        $product = new Product4();

        $product->setId(10);
        $product->setStock(100);
        $violation = $validator->validate($product);
        $this->assertEquals(0,count($violation));

        $product->setId(11);
        $product->setStock(100);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals('id',$violation['id'][0]->getPropertyPath());
        $this->assertEquals(11,$violation['id'][0]->getInvalidValue());
        $this->assertEquals('AnerisTest\ValidatorValidatorTest\Product4',get_class($violation['id'][0]->getRootBean()));

        $product->setId(null);
        $product->setStock(100);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("may not be null.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("may not be null.",$violation['id'][0]->getMessage());

        $product->setId(10);
        $product->setStock(101);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $product->setId(11);
        $product->setStock(101);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $product->setId(null);
        $product->setStock(101);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("may not be null.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("may not be null.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $product->setId(10);
        $product->setStock(null);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("may not be null.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("may not be null.",$violation['stock'][0]->getMessage());

        $product->setId(11);
        $product->setStock(null);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("may not be null.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("may not be null.",$violation['stock'][0]->getMessage());

        $product->setId(null);
        $product->setStock(null);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("may not be null.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("may not be null.",$violation['id'][0]->getMessage());
        $this->assertEquals("may not be null.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("may not be null.",$violation['stock'][0]->getMessage());
    }

    public function testValidateBean()
    {
        $validator = new Validator();

        $product = new Product();
        $product->setId(11);
        $product->setId2(9);
        $product->setStock(101);

        $violation = $validator->validate($product);
        $this->assertEquals(3,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be greater than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be greater than or equal to 10.",$violation['id2'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $product->setId(null);
        $product->setId2(null);
        $product->setStock(100);
        $violation = $validator->validate($product);
        $this->assertEquals(0,count($violation));

        $product->setId2(0);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be greater than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be greater than or equal to 10.",$violation['id2'][0]->getMessage());
    }

    public function testMessage()
    {
        $validator = new Validator();

        $product = new Product2();
        $product->setId(1);
        $product->setStock(101);

        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be greater than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be greater than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("stock max is {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("stock max is 100.",$violation['stock'][0]->getMessage());
    }

    public function testMultiParam()
    {
        $validator = new Validator();

        $product = new Product3();
        $product->setId(11);
        $product->setName('abcdefghijk');
        $product->setDescription('abcd');
        $product->setCode('abcdefghijk');

        $violation = $validator->validate($product);
        $this->assertEquals(4,count($violation));
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("name max length is 10.",$violation['name'][0]->getMessage());
        $this->assertEquals("description min length is 5.",$violation['description'][0]->getMessage());
        $this->assertEquals("code length is 5 - 10.",$violation['code'][0]->getMessage());

        $product->setId(10);
        $product->setName('abcdefghij');
        $product->setDescription('abcde');
        $product->setCode('abcdefghij');
        $violation = $validator->validate($product);
        $this->assertEquals(0,count($violation));

        $product->setCode('abcd');
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("code length is 5 - 10.",$violation['code'][0]->getMessage());
    }

    public function testI18nTranslator()
    {
        $translator = new Translator();
        $translator->bindTextDomain(
            Validator::getTranslatorTextDomain(),
            Validator::getTranslatorBasePath()
        );
        $translator->setLocale('ja_JP');
        $translator->setTextDomain(Validator::getTranslatorTextDomain());

        $validator = new Validator($translator);

        $product = new Product();
        $product->setId(11);
        $product->setStock(101);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        //echo $this->encodeConsoleCode($violation['id']->getMessage());
        $this->assertEquals('10以下でなければなりません。',$violation['id'][0]->getMessage());

        $translator->setLocale('en_US');
        $validator = new Validator($translator);

        $product = new Product();
        $product->setId(11);
        $product->setStock(101);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        //echo $this->encodeConsoleCode($violation['id']->getMessage());
        $this->assertEquals('must be less than or equal to 10.',$violation['id'][0]->getMessage());

    }

    public function testI18nTranslatorZF2()
    {
        $translator = new ZendTranslator();
        $translator->addTranslationFilePattern(
            'Gettext',
            Validator::getTranslatorBasePath(),
            Validator::getTranslatorFilePattern()
        );
        $translator->setLocale('ja_JP');

        $validator = new Validator($translator);

        $product = new Product();
        $product->setId(11);
        $product->setStock(101);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        //echo $this->encodeConsoleCode($violation['id']->getMessage());
        $this->assertEquals('10以下でなければなりません。',$violation['id'][0]->getMessage());

        $translator->setLocale('en_US');
        $validator = new Validator($translator);

        $product = new Product();
        $product->setId(11);
        $product->setStock(101);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        //echo $this->encodeConsoleCode($violation['id']->getMessage());
        $this->assertEquals('must be less than or equal to 10.',$violation['id'][0]->getMessage());
    }

    public function testCache()
    {
        // for non cache
        $validator = new Validator();

        $product = new Product();
        $product->setId(11);
        $product->setStock(101);

        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $product->setId(10);
        $product->setStock(100);
        $violation = $validator->validate($product);
        $this->assertEquals(0,count($violation));

        // for cache
        $validator = new Validator();

        $product = new Product();
        $product->setId(11);
        $product->setStock(101);

        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $product->setId(10);
        $product->setStock(100);
        $violation = $validator->validate($product);
        $this->assertEquals(0,count($violation));
    }

    public function testTargetMethod()
    {
        $validator = new Validator();

        $product = new Product5();
        $product->setId(10);
        $product->setPassword('aaaaaaaa');
        $product->setPasswordAgain('bbbbbbbbb');

        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("Passwords do not match.",$violation['password'][0]->getMessage());

        $product->setPasswordAgain('aaaaaaaa');
        $violation = $validator->validate($product);
        $this->assertEquals(0,count($violation));
    }

    public function testPropertyAccess()
    {
        $validator = new Validator();

        $product = new Product6();
        $product->id = 11;
        $product->stock = 101;

        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $product->id = 10;
        $product->stock = 100;
        $violation = $validator->validate($product);
        $this->assertEquals(0,count($violation));
    }

    public function testGroup()
    {
        $validator = new Validator();

        $product = new Product7();
        $product->setId(11);
        $product->setId2(21);
        $product->setStock(101);

        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $violation = $validator->validate($product,'a');
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 20.",$violation['id2'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $violation = $validator->validate($product,array('Default','a'));
        $this->assertEquals(3,count($violation));

        $violation = $validator->validate($product,array('c'));
        $this->assertEquals(0,count($violation));

        $product->setId2(31);
        $violation = $validator->validate($product,array('c'));
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 30.",$violation['id2'][0]->getMessage());
    }

    public function testGroupSequence()
    {
        $validator = new Validator();

        $product = new Product7();
        $product->setId(11);
        $product->setId2(21);
        $product->setStock(101);

        $seq = new GroupSequence(array(
            'Default','a'
        ));
        $violation = $validator->validate($product,$seq);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());
    }

    public function testGroupSequence2()
    {
        $validator = new Validator();

        $product = new Product10();
        $product->setId(11);
        $product->setId2(11);
        $product->setId3(11);

        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to 10.",$violation['id3'][0]->getMessage());

        $violation = $validator->validate($product,'Default');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to 10.",$violation['id3'][0]->getMessage());

        $product->setId3(10);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to 10.",$violation['id2'][0]->getMessage());

        $product->setId2(10);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());

        $product->setId(10);
        $violation = $validator->validate($product);
        $this->assertEquals(0,count($violation));
    }

    public function testValidateProperty()
    {
        $validator = new Validator();

        $product = new Product7();
        $product->setId(11);
        $product->setId2(21);
        $product->setStock(101);

        $violation = $validator->validateProperty($product,'stock');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $violation = $validator->validateProperty($product,'stock','b');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be greater than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be greater than or equal to 110.",$violation['stock'][0]->getMessage());

        $product->setStock(100);
        $violation = $validator->validateProperty($product,'stock');
        $this->assertEquals(0,count($violation));

    }

    public function testValidateValue()
    {
        $validator = new Validator();

        $className = 'AnerisTest\ValidatorValidatorTest\Product7';

        $violation = $validator->validateValue($className,'stock',101);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $violation = $validator->validateValue($className,'stock',101,'b');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be greater than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be greater than or equal to 110.",$violation['stock'][0]->getMessage());

        $violation = $validator->validateValue($className,'stock',100);
        $this->assertEquals(0,count($violation));

    }

    public function testGetConstraints()
    {
        $validator = new Validator();

        $className = 'AnerisTest\ValidatorValidatorTest\Product7';
        $constraints = $validator->getConstraintsForClass($className);
        $this->assertEquals(3,count($constraints));
        //print_r($constraints);
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($constraints['id'][0]->constraint));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($constraints['id2'][0]->constraint));
        $this->assertEquals('Aneris\Validator\Constraints\Max',get_class($constraints['stock'][0]->constraint));
        $this->assertEquals('Aneris\Validator\Constraints\Min',get_class($constraints['stock'][1]->constraint));
    }

    public function testNest()
    {
        $validator = new Validator();

        $product = new Product8();
        $product->setId(11);
        $product->setId2(11);

        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());

        $violation = $validator->validate($product,'c');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id2'][0]->getMessage());

        $product->setId2(21);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 20.",$violation['id2'][0]->getMessage());
    }

    public function testNest2()
    {
        $validator = new Validator();

        $product = new Product9();
        $product->setId(11);
        $product->setId2(11);

        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());

        $violation = $validator->validate($product,'c');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id2'][0]->getMessage());

        $product->setId2(21);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 20.",$violation['id2'][0]->getMessage());

        $product->setId(null);
        $product->setId2(null);
        $product->setId3(11);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id3'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id3'][0]->getMessage());
    }

    public function testDoctrineAnnotationReader()
    {
        DoctrinePortability::patch();

        $validator = new Validator();
        $validator->getConstraintManager()
            ->setAnnotationReader(new DoctrineAnnotationReader());

        $product = new Product();
        $product->setId(11);
        $product->setId2(9);
        $product->setStock(101);

        $violation = $validator->validate($product);
        $this->assertEquals(3,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be greater than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be greater than or equal to 10.",$violation['id2'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['stock'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 100.",$violation['stock'][0]->getMessage());

        $product->setId(null);
        $product->setId2(null);
        $product->setStock(100);
        $violation = $validator->validate($product);
        $this->assertEquals(0,count($violation));

        $product->setId2(0);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be greater than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be greater than or equal to 10.",$violation['id2'][0]->getMessage());
    }

    public function testDoctrineAnnotationReaderForNest2Constraint()
    {
        DoctrinePortability::patch();

        $validator = new Validator();
        $validator->getConstraintManager()
            ->setAnnotationReader(new DoctrineAnnotationReader());

        $product = new Product9();
        $product->setId(11);
        $product->setId2(11);

        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());

        $violation = $validator->validate($product,'c');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id2'][0]->getMessage());

        $product->setId2(21);
        $violation = $validator->validate($product);
        $this->assertEquals(2,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id'][0]->getMessage());
        $this->assertEquals("must be less than or equal to {value}.",$violation['id2'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 20.",$violation['id2'][0]->getMessage());

        $product->setId(null);
        $product->setId2(null);
        $product->setId3(11);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to {value}.",$violation['id3'][0]->getMessageTemplate());
        $this->assertEquals("must be less than or equal to 10.",$violation['id3'][0]->getMessage());
    }

    public function testOnModule()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Validator\Module' => true,
                    'Aneris\Stdlib\I18n\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $translator = $sm->get('Aneris\Stdlib\I18n\Translator');
        $translator->setLocale('ja_JP');
        $validator = $sm->get('Aneris\Validator\Validator');

        $product = new Product();
        $product->setId(11);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals('10以下でなければなりません。',$violation['id'][0]->getMessage());
    }

    public function testOnModuleWithoutTranslator()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Validator\Module' => true,
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $validator = $sm->get('Aneris\Validator\Validator');

        $product = new Product();
        $product->setId(11);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals('must be less than or equal to 10.',$violation['id'][0]->getMessage());
    }

    public function testOnModuleWithTranslatorZF2()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Validator\Module' => true,
                ),
            ),
            'validator' => array(
                'translator' => 'Zend\I18n\Translator\Translator',
                'translator_text_domain' => 'validator',
            ),
            'container' => array(
                'components' => array(
                    'Aneris\Validator\Validator' => array(
                        'constructor_args' => array(
                            'translator' => array('ref' => 'Zend\I18n\Translator\Translator'),
                        ),
                    ),
                    'Zend\I18n\Translator\Translator' => array(
                        'factory' => function($sm) {
                            $config = $sm->get('config');
                            $config = isset($config['translator']) ? $config['translator'] : array();
                            $translator = ZendTranslator::factory($config);
                            return $translator;
                        }
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $translator = $sm->get('Zend\I18n\Translator\Translator');
        $translator->setLocale('ja_JP');
        $validator = $sm->get('Aneris\Validator\Validator');

        $product = new Product();
        $product->setId(11);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
        $this->assertEquals('10以下でなければなりません。',$violation['id'][0]->getMessage());
    }

    public function testSymfonyValidator()
    {
        // MUST LOAD CONSTRAINT CLASS BEFORE VALIDATE WHEN IT USE Doctrine's Annotation Reader!!
        //$dmy = new Symfony\Component\Validator\Constraints\Length(array('min'=>10));
        //$reader = new Doctrine\Common\Annotations\AnnotationReader();

        $reader = new AnnotationReaderProxy();
        $validatorBuilder = new SymfonyValidatorBuilder();
        $validatorBuilder->enableAnnotationMapping(
            $reader
        );
        $validator = $validatorBuilder->getValidator();
        $product = new ProductSymfony();
        $product->setId(11);
        $product->setId2(10);
        $violation = $validator->validate($product);
        $this->assertEquals(1,count($violation));
    }
}
