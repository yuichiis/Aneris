<?php
namespace AnerisTest\ValidatorConstraintsTest;

use Aneris\Stdlib\Entity\EntityInterface;
use Aneris\Stdlib\Entity\EntityTrait;
use Aneris\Validator\Validator;

// Test Target Classes are under the namespace flowing;
use Aneris\Validator\Constraints as Assert;


class Product implements EntityInterface
{
    use EntityTrait;
    /** @Assert\AssertTrue **/
    protected $assertTrue;
    /** @Assert\AssertFalse **/
    protected $assertFalse;
    /** @Assert\Max(100) **/
    protected $max;
    /** @Assert\Min(100) **/
    protected $min;
    /** @Assert\NotNull **/
    protected $notNull;
    /** @Assert\Null **/
    protected $null;
    /** @Assert\Size(min=4,max=8) **/
    protected $size;
    /** @Assert\Pattern(regexp="/^[A-Z]+$/") **/
    protected $regexp;
    /** @Assert\Pattern(regexp="^[A-Z]+$",flags={CASE_INSENSITIVE}) **/
    protected $regexp2;
    /** @Assert\Email **/
    protected $email;
    /** @Assert\Length(min=4,max=8) **/
    protected $length;
    /** @Assert\NotBlank **/
    protected $notBlank;
    /** @Assert\Digits(integer=4,fraction=2) **/
    protected $digits;
    /** @Assert\Date **/
    protected $date;
    /** @Assert\DateTimeLocal **/
    protected $datetimelocal;
    /** @Assert\Future **/
    public function future()
    {
        $timezone = '+900';
        $parts = explode('.',$this->datetimelocal);
        $w3cTime = $parts[0].$timezone;
        return $w3cTime;
    }
    /** @Assert\Past **/
    public function past()
    {
        $timezone = '+900';
        $parts = explode('.',$this->datetimelocal);
        $w3cTime = $parts[0].$timezone;
        return $w3cTime;
    }
}

class ValidatorConstraintsTest extends \PHPUnit_Framework_TestCase
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

    public function testAssertTrue()
    {
        $validator = new Validator();

        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'assertTrue',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'assertTrue',true);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'assertTrue',false);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be true.",$violation['assertTrue'][0]->getMessage());
    }

    public function testAssertFalse()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'assertFalse',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'assertFalse',false);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'assertFalse',true);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be false.",$violation['assertFalse'][0]->getMessage());
    }

    public function testMax()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'max',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'max',100);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'max',101);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be less than or equal to 100.",$violation['max'][0]->getMessage());
    }

    public function testMin()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'min',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'min',100);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'min',99);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be greater than or equal to 100.",$violation['min'][0]->getMessage());
    }

    public function testNotNull()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'notNull',1);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'notNull',0);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'notNull',null);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("may not be null.",$violation['notNull'][0]->getMessage());
    }

    public function testNull()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'null',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'null',1);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be null.",$violation['null'][0]->getMessage());
        $violation = $validator->validateValue($className,'null',0);
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be null.",$violation['null'][0]->getMessage());
    }

    public function testSize()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'size',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'size','');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'size','abcd');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'size','abc');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("size must be between 4 and 8.",$violation['size'][0]->getMessage());
        $violation = $validator->validateValue($className,'size','abcdefgh');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'size','abcdefghi');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("size must be between 4 and 8.",$violation['size'][0]->getMessage());

        $violation = $validator->validateValue($className,'size',array(1,2,3,4));
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'size',array(1,2,3));
        $this->assertEquals(1,count($violation));
        $this->assertEquals("size must be between 4 and 8.",$violation['size'][0]->getMessage());
        $violation = $validator->validateValue($className,'size',array(1,2,3,4,5,6,7,8));
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'size',array(1,2,3,4,5,6,7,8,9));
        $this->assertEquals(1,count($violation));
        $this->assertEquals("size must be between 4 and 8.",$violation['size'][0]->getMessage());
        $violation = $validator->validateValue($className,'size',array());
        $this->assertEquals(1,count($violation));
        $this->assertEquals("size must be between 4 and 8.",$violation['size'][0]->getMessage());
    }

    public function testRegexp()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'regexp',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'regexp','');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'regexp','ABC');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'regexp','abc');
        $this->assertEquals(1,count($violation));
        $this->assertEquals('must match "/^[A-Z]+$/"',$violation['regexp'][0]->getMessage());

        $violation = $validator->validateValue($className,'regexp2','ABC');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'regexp2','abc');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'regexp2','123');
        $this->assertEquals(1,count($violation));
        $this->assertEquals('must match "^[A-Z]+$"',$violation['regexp2'][0]->getMessage());
    }

    public function testEmail()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'email',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'email','');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'email','abc');
        $this->assertEquals(1,count($violation));
        $this->assertEquals('not a well-formed email address.',$violation['email'][0]->getMessage());
        $violation = $validator->validateValue($className,'email','@abc.com');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','.@abc.com');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','abc.@abc.com');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','abc@abc.com');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'email','abc.def@abc.com');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'email','ab-c.def@abc.com');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'email','abc@.abc.com');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','abc@localhost');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','abc@abc.123');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','abc@1ab.com');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','abc@-ab.com');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','abc@a3.com');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'email','abc@a-3.com');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'email','abc@a3-.com');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','abc@abc.abc.com');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'email','abc@1.abc.com');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'email','abc@-.abc.com');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','abc@-1.abc.com');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'email','abc@a-1.abc.com');
        $this->assertEquals(0,count($violation));
    }

    public function testLength()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'length',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'length','');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'length','abcd');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'length','abc');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("length must be between 4 and 8.",$violation['length'][0]->getMessage());
    }

    public function testNotBlank()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'notBlank',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'notBlank','');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("may not be blank.",$violation['notBlank'][0]->getMessage());
        $violation = $validator->validateValue($className,'notBlank','abcd');
        $this->assertEquals(0,count($violation));
    }

    public function testDigits()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'digits',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'digits','');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'digits','1234');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'digits','.12');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'digits','abc');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("numeric value out of bounds (<4 digits>.<2 digits> expected)",$violation['digits'][0]->getMessage());
        $violation = $validator->validateValue($className,'digits','1.2.3');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'digits','12345');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'digits','1234.');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'digits','1234.12');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'digits','1234.ab');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'digits','1234.123');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'digits','-123');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'digits','1234.-1');
        $this->assertEquals(1,count($violation));
    }

    public function testDate()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'date',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'date','');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'date',new \DateTime('2000-01-31'));
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'date','2000-01-31');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'date','2000-01');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be a valid date.",$violation['date'][0]->getMessage());
        $violation = $validator->validateValue($className,'date','--');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'date','a-a-a');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'date','100-100-100');
        $this->assertEquals(1,count($violation));
    }

    public function testDateTimeLocal()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'datetimelocal',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal',new \DateTime('2000-01-31'));
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T23:59:59');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T');
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be a valid date and time.",$violation['datetimelocal'][0]->getMessage());
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T0:0:0T0');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T::');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31Ta:a:a');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T24:59:59');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T23:60:59');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T23:59:60');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T23:59');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T23:59:59.123');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T23:59:59.12.3');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T23:59:59.a');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'datetimelocal','2000-01-31T23:59:a.59');
        $this->assertEquals(1,count($violation));
    }

    public function testFuture()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'future',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'future','');
        $this->assertEquals(0,count($violation));

        $violation = $validator->validateValue($className,'future',new \DateTime('@'.(time()+3600)));
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'future',new \DateTime('@'.(time()-3600)));
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be in the future.",$violation['future'][0]->getMessage());
        $violation = $validator->validateValue($className,'future',new \DateTime('@'.(time()-3600)));
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'future','2030-12-25T23:59:59+900');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'future','2000-12-25T23:59:59+900');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'future',time()+3600);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'future',time()-3600);
        $this->assertEquals(1,count($violation));
    }

    public function testPast()
    {
        $validator = new Validator();
        $className = "AnerisTest\ValidatorConstraintsTest\Product";

        $violation = $validator->validateValue($className,'past',null);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'past','');
        $this->assertEquals(0,count($violation));

        $violation = $validator->validateValue($className,'past',new \DateTime('@'.(time()-3600)));
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'past',new \DateTime('@'.(time()+3600)));
        $this->assertEquals(1,count($violation));
        $this->assertEquals("must be in the past.",$violation['past'][0]->getMessage());
        $violation = $validator->validateValue($className,'past',new \DateTime('@'.(time()+3600)));
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'past','2000-12-25T23:59:59+900');
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'past','2030-12-25T23:59:59+900');
        $this->assertEquals(1,count($violation));
        $violation = $validator->validateValue($className,'past',time()-3600);
        $this->assertEquals(0,count($violation));
        $violation = $validator->validateValue($className,'past',time()+3600);
        $this->assertEquals(1,count($violation));
    }
}
