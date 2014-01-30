<?php
namespace AnerisTest\PhpTest;

use stdClass;
use ReflectionClass;

class BaseClass
{
	public function getNamespace()
	{
		return __NAMESPACE__;
	}

	public function getTrace()
	{
		return debug_backtrace();
	}
}
interface FooInterface
{
	const Boo = 'Boooo!';
	//public $bar;
}

class SubClass extends BaseClass implements FooInterface
{
}

interface SubInterface extends FooInterface
{
}

class ClassWithSubInterface implements SubInterface
{}

interface BarInterface extends FooInterface
{
    public function FunctionName($value='');
}

class ClassWithDuplicatFooInterface implements BarInterface,SubInterface
{
    public function FunctionName($value='')
    {
        # code...
    }
}


class ProtectedConstruct
{
	protected function __construct($argument)
	{
		# code...
	}
}

class SubProtectedConstruct extends ProtectedConstruct
{
	public function __construct($argument)
	{
		# code...
	}
}

class Prototyping
{
    public function FunctionName(
        array $arrayVar,
        callable $callableVar)
    {
        # code...
    }
}

interface HaveReferenceParamInterface
{
    public function func(array &$foo);
}
class HaveReferenceParam implements HaveReferenceParamInterface
{
    public function func(array &$foo)
    {
        $foo[] = 'foo';
    }
}

class PhpTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
		\Aneris\Stdlib\Cache\CacheFactory::clearFileCache();
    }
    public static function tearDownAfterClass()
    {
		\Aneris\Stdlib\Cache\CacheFactory::clearFileCache();
        putenv("LC_ALL");
        putenv("LC_MESSAGES");
        setlocale(LC_ALL, null);
		if(defined("LC_MESSAGES"))
	        setlocale(LC_MESSAGES, null);
    }
	//public function testNamespaceAtParentClass()
	//{
	//	$obj = new Aneris2Php2Test\SubClass();
	//	$this->assertEquals('Aneris2PhpTest',$obj->getNamespace()) ;
	//}

    public function testApcExtension()
    {
        if(!extension_loaded('apc'))
            return;
        $this->assertTrue(extension_loaded('apc'));
        $this->assertTrue(function_exists('apc_fetch'));
        $this->assertEquals('1',ini_get('apc.enable_cli'));
        $bar = 'BAR';
        apc_add('foo', $bar);
        $this->assertEquals('BAR',apc_fetch('foo'));
        $bar = 'NEVER GETS SET';
        apc_add('foo', $bar);
        $this->assertEquals('BAR',apc_fetch('foo'));
        //echo sys_get_temp_dir();
        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj2->var = 'var';
        $obj1->obj2 = $obj2;
        $obj2id = spl_object_hash($obj2);
        apc_add('obj1',$obj1);
        unset($obj2);
        unset($obj1);
        $obj3 = apc_fetch('obj1');
        $this->assertEquals($obj2id,spl_object_hash($obj3->obj2));
    }

	public function testStringControlCode()
	{
        //$this->assertTrue(false);
		//echo '\0';
		//echo "\0";
		//echo '\x00';
		//echo "\x00";
		$cachePath = \Aneris\Stdlib\Cache\CacheFactory::$fileCachePath;
		$className = 'Aneris\\Cache\\FileCache';
		$definition = "\\A'\\'\\\\\0\n\r\t\x22";
		$value = "<?php\nreturn unserialize('".str_replace(array('\\','\''), array('\\\\','\\\''), serialize($definition))."');";
        $filename = $cachePath . '/def/' . str_replace('\\', '/', $className) . '.php';
        if(!is_dir(dirname($filename)))
            mkdir(dirname($filename),0777,true);
        file_put_contents($filename, $value);
        $result = require $filename;
        $this->assertEquals($definition,$result);
	}
	public function testgettext()
	{
		$this->assertTrue(function_exists('gettext'));
		//$this->assertEquals('messages',textdomain(null));
        textdomain('messages');
		$this->assertEquals(getcwd(),bindtextdomain('messages',null));
		//$locale = 'ja_JP';
		$locale_dir = ANERIS_TEST_RESOURCES.'/php/messages';
		//putenv("LANGUAGE=$locale");
		//putenv("LC_ALL=ja_JP");
		//putenv("LC_ALL=en_US");
		//putenv("LC_MESSAGES=ja_JP");
		//if(defined("LC_MESSAGES"))
		//	setlocale(LC_MESSAGES, 'en_US');
		//else
		//	setlocale(LC_ALL, 'en_US');
		bindtextdomain('messages', $locale_dir);
		//bindtextdomain('messages', $locale_dir.'2');
		//textdomain('messages');
		putenv("LC_ALL");
		putenv("LC_MESSAGES=en_US");
		if(defined("LC_MESSAGES")) {
			setlocale(LC_ALL, null);
			setlocale(LC_MESSAGES, null);
		}
		$text = gettext("{aneris.test.phptest.gettext.messages}");
		$this->assertEquals('Welcome to My PHP Application',$text);
		putenv("LC_MESSAGES=ja_JP");
		if(defined("LC_MESSAGES")) {
			setlocale(LC_MESSAGES, null);
		}
		$text = gettext("{aneris.test.phptest.gettext.messages}");
		$this->assertEquals('My PHP Application he youkoso',$text);
		// LC_ALL is first priority
		putenv("LC_ALL=en_US");
		putenv("LC_MESSAGES=ja_JP");
		if(defined("LC_MESSAGES")) {
			setlocale(LC_ALL, null);
			setlocale(LC_MESSAGES, null);
		}
		$text = gettext("{aneris.test.phptest.gettext.messages}");
		$this->assertEquals('Welcome to My PHP Application',$text);
		// LC_ALL is first priority
		putenv("LC_ALL=ja_JP");
		putenv("LC_MESSAGES=en_US");
		if(defined("LC_MESSAGES")) {
			setlocale(LC_ALL, null);
			setlocale(LC_MESSAGES, null);
		}
		$text = gettext("{aneris.test.phptest.gettext.messages}");
		$this->assertEquals('My PHP Application he youkoso',$text);
		putenv("LC_ALL");
		putenv("LC_MESSAGES");
		if(defined("LC_MESSAGES")) {
			setlocale(LC_ALL, null);
			setlocale(LC_MESSAGES, null);
		}
		if(defined("LC_MESSAGES")) {
		    setlocale(LC_MESSAGES, 'en_US');
			$text = gettext("{aneris.test.phptest.gettext.messages}");
			$this->assertEquals('Welcome to My PHP Application',$text);
		    setlocale(LC_MESSAGES, NULL);
		}
		// setlocale(LC_ALL) do not work if there is not LC_MESSAGES constant.
		putenv("LC_ALL=ja_JP");
		setlocale(LC_ALL, 'en_US');
		$text = gettext("{aneris.test.phptest.gettext.messages}");
		if(!defined("LC_MESSAGES"))
			$this->assertEquals('My PHP Application he youkoso',$text);
		else
			$this->assertEquals('Welcome to My PHP Application',$text);
		putenv("LC_ALL=en_US");
		setlocale(LC_ALL, 'ja_JP');
		$text = gettext("{aneris.test.phptest.gettext.messages}");
		if(!defined("LC_MESSAGES"))
			$this->assertEquals('Welcome to My PHP Application',$text);
		else
			$this->assertEquals('My PHP Application he youkoso',$text);
		putenv("LC_ALL=en_US");
		setlocale(LC_ALL, null);
		$text = gettext("{aneris.test.phptest.gettext.messages}");
		$this->assertEquals('Welcome to My PHP Application',$text);
	}
    public function testSerialize()
    {
        //$this->assertEquals(3, count($instance->logData));
        //echo $ser;
        $a = array(
            'D' => ANERIS_TEST_RESOURCES,
        );
        //echo serialize($a);
        //echo var_export($a);
    }
    public function testYaml()
    {
    	if(!extension_loaded('yaml'))
    		return;
        $yaml = <<<EOD
---
definitions:
  - class: Aneris2\\DiTestParam2
    initiator: __construct
    paramaters: 
    - { name: abc , value: 123 }
    - { name: xyz , ref: Aneris\\Service\\ServiceManager }
  - class: Aneris2\\DiTestParam2
    initiator: __construct
    paramaters: 
    - { name: abc , value: 123 }
    - { name: xyz , ref: Aneris\\Service\\ServiceManager }

paramaters:
  Aneris2DiTestParam2:
    arg1: xyz

routes:
  home: 
    path: /
    defaults:
      namespace: Foo\\Space
      controller: Index
      action: index
    type: literal
    options: [ action , id ]

dispatcher:
  invokables:
    Foo\\Space\\Index: Aneris2MvcApplicationTest\\Controller\\FooController

view:
  template_paths:
  - "%__DIR__%/twig/templates"

EOD;
        $yaml = str_replace('%__DIR__%', str_replace('\\','\\\\',ANERIS_TEST_RESOURCES),$yaml);
        $parsed = yaml_parse($yaml); 
        //echo "\n";
        //var_export($parsed);
        //echo "\n";
        //var_export(array( 'a' => 'a\b\c' ));
        //echo "\n";
    }
    public function testSplDoublyLinkedList()
    {
    	$list = new \SplDoublyLinkedList();
    	$list->push('a');
    	$this->assertEquals('a',$list[0]);
    	$list->push('b');
    	$this->assertEquals('a',$list[0]);
    	$this->assertEquals('b',$list[1]);
    	$list[0] = 'c';
    	$this->assertEquals('c',$list[0]);
    }
    /**
     * @expectedException        OutOfRangeException
     * @expectedExceptionMessage Offset invalid or out of range
     */
    public function testSplDoublyLinkedListError()
    {
    	$list = new \SplDoublyLinkedList();
    	$list[0] = 'c';
    }
    public function testTokenGetAll()
    {
    	$doc = <<<EOD
/**
 * @Entity @Table(name="products") @Test(t1="abc",t2="zz,z",t3="x=xx",t4=true)
   @Pattern.List({@Pattern(regexp = "^aaa.*"),@Pattern(regexp = ".*bbb$")})
   @Target({ FIELD , METHOD })
   @NotNull(groups = { Group1.class, Default.class })
 */
EOD;
		$c = preg_match('@^/\\*\\*(.*)\\*/$@s', $doc,$match);
		if($c) {
			$doc = "<?php\n" . trim($match[1]); 
			//echo $doc."\n";
		    $tokens = token_get_all($doc);
		    //print_r($tokens);
		    foreach($tokens as $token) {
	    		if(is_numeric($token[0])) {
		    		//echo token_name($token[0]).':';
		    		//echo $token[1].":";
		    		//echo $token[2]."\n";
	    		} else {
		    		//echo '"'.$token[0].'"'."\n";
	    		}
		    }
		}
    }

    public function test_is_subclass_of()
    {
    	$baseclass = new BaseClass();
    	$this->assertFalse(is_subclass_of($baseclass,'AnerisTest\PhpTest\BaseClass'));
    	$subclass = new SubClass();
    	$this->assertTrue(is_subclass_of($subclass,'AnerisTest\PhpTest\BaseClass'));
    	$this->assertTrue(is_subclass_of($subclass,'AnerisTest\PhpTest\FooInterface'));
    }

    public function testProtectedConstruct()
    {
    	$classRef = new ReflectionClass('AnerisTest\PhpTest\ProtectedConstruct');
    	$this->assertFalse($classRef->getConstructor()->isPublic());
    	//$super = new ProtectedConstruct(1);
    	$sub = new SubProtectedConstruct(1);
    }

    public function testMagicConstant()
    {
    	$this->assertEquals('AnerisTest\PhpTest\PhpTest::testMagicConstant',__METHOD__);
    	$this->assertEquals('AnerisTest\PhpTest\PhpTest',__CLASS__);
    	$this->assertEquals('testMagicConstant',__FUNCTION__);
    }

    public function testDebugTrace()
    {
    	$o = new BaseClass();
    	$trace = $o->getTrace();
    	$this->assertEquals('AnerisTest\PhpTest\PhpTest',$trace[1]['class']);
    	$this->assertEquals('testDebugTrace',$trace[1]['function']);
    	$this->assertEquals('->',$trace[1]['type']);
    	$this->assertEquals(array(),$trace[1]['args']);
    }

    public function testExtendsReflection()
    {
        $interfaces = class_implements('AnerisTest\PhpTest\ClassWithDuplicatFooInterface');
        $result = array(
            'AnerisTest\PhpTest\BarInterface' => 'AnerisTest\PhpTest\BarInterface',
            'AnerisTest\PhpTest\FooInterface' => 'AnerisTest\PhpTest\FooInterface',
            'AnerisTest\PhpTest\SubInterface' => 'AnerisTest\PhpTest\SubInterface',
        );
        $this->assertEquals($result, $interfaces);
        $copy = $interfaces;
        foreach ($copy as $interface) {
            foreach(class_implements($interface) as $parent) {
                unset($interfaces[$parent]);
            }
        }
        $result = array(
            'AnerisTest\PhpTest\BarInterface' => 'AnerisTest\PhpTest\BarInterface',
            'AnerisTest\PhpTest\SubInterface' => 'AnerisTest\PhpTest\SubInterface',
        );
        $this->assertEquals($result, $interfaces);
    }

    public function testPrototyping()
    {
        $i = new Prototyping();
        $callable = array($i,'FunctionName');
        $arrayVar = array();
        $i->FunctionName($arrayVar,$callable);
    }

    /**
     * @expectedException        PHPUnit_Framework_Error
     * @expectedExceptionMessage Argument 2 passed to AnerisTest\PhpTest\Prototyping::FunctionName() must be callable, array given, called in
     */
    public function testPrototypingError()
    {
        $i = new Prototyping();
        $callable = array($i,'None');
        $arrayVar = array();
        $i->FunctionName($arrayVar,$callable);
    }

    public function testReferenceParam()
    {
        $o = new HaveReferenceParam();
        $a = array('A');
        $o->func($a);
        $this->assertEquals(array('A','foo'),$a);
        call_user_func_array(array($o,'func'), array(&$a));
        $this->assertEquals(array('A','foo','foo'),$a);
    }
}
