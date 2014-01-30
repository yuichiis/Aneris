<?php
namespace AnerisTest\AnnotationParserTest;

use Aneris\Stdlib\Cache\CacheFactory;
// Test Target Classes
use Aneris\Annotation\Parser;
use Aneris\Annotation\ElementType;

class DummyAnnotationManager extends \Aneris\Annotation\AnnotationManager
{
	public function createAnnotation($annotationName,$args,$location)
	{
		return array(
			'annotation' => $annotationName,
			'args' => $args,
			'location' => $location,
		);
	}
}

class AnnotationParserTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
		\Aneris\Stdlib\Cache\CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/annotation/cache');
    }

    public static function tearDownAfterClass()
    {
		\Aneris\Stdlib\Cache\CacheFactory::clearFileCache(CacheFactory::$fileCachePath.'/annotation/cache');
    }

    public function setUp()
    {
    }

    public function testComplex()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::TYPE,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'NameName',
			'uri'    => 'UriUri',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * @Entity
 * @Table(name="products")
 * @Test(t1="abc",t2="zz,z",t3="x=xx",t4=true)
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
		  0 =>
		  array (
		    'annotation' => 'Entity',
		    'args' => null,
		    'location' => array(
			    'target' => 'TYPE',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'NameName',
			    'uri' => 'UriUri',
				'filename' => 'filename.php',
		    ),
		  ),
		  1 =>
		  array (
		    'annotation' => 'Table',
			'args' => 
		    array (
		      'name' => 'products',
		    ),
		    'location' => array(
			    'target' => 'TYPE',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'NameName',
			    'uri' => 'UriUri',
				'filename' => 'filename.php',
		    ),
		  ),
		  2 =>
		  array (
		    'annotation' => 'Test',
		    'args' =>
		    array (
		      't1' => 'abc',
		      't2' => 'zz,z',
		      't3' => 'x=xx',
		      't4' => true,
		    ),
		    'location' => array(
			    'target' => 'TYPE',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'NameName',
			    'uri' => 'UriUri',
				'filename' => 'filename.php',
		    ),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testSingle()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);

        $docComment = <<<EOD
/**
 * @Id
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
		  0 =>
		  array (
		    'annotation' => 'Id',
		    'args' => null,
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testEmptyArg()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * @Abc()
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);

		$result = array(
		  0 =>
		  array (
		    'annotation' => 'Abc',
		    'args' => null,
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testArgAndValueString()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * @Column2(type=integer)
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);

		$result = array(
		  0 =>
		  array (	
		    'annotation' => 'Column2',
		    'args' =>
		    array (
		    	'type' => 'integer',
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testNoNameArg()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * @Min("10") 
 * @Max(100)
 * @Max(true)
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);

		$result = array(
		  0 =>
		  array (
		    'annotation' => 'Min',
		    'args' => '10',
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		  1 =>
		  array (
		    'annotation' => 'Max',
		    'args' => '100',
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		  2 =>
		  array (
		    'annotation' => 'Max',
		    'args' => true,
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testNoNameMultiArg()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * @Limit(min="10",max=100)
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);

		$result = array(
		  0 =>
		  array (
		    'annotation' => 'Limit',
		    'args' =>
		    array (
		    	'min' => '10',
		    	'max' => '100',
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testNoNameMultiArgSingle()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * @Limit(min=1)
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);

		$result = array(
		  0 =>
		  array (
		    'annotation' => 'Limit',
		    'args' =>
		    array (
		    	'min' => '1',
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testArgAndValueStringWithSpace()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * @Id
 * @Column2(type="int eger")
 * @Max(max="100")
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);

		$result = array(
		  0 =>
		  array (
		    'annotation' => 'Id',
		    'args' => null,
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		  1 =>
		  array (
		    'annotation' => 'Column2',
		    'args' =>
		    array (
		    	'type' => 'int eger',
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		  2 =>
		  array (
		    'annotation' => 'Max',
		    'args' =>
		    array (
		    	'max' => '100',
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testArgAndValueStringWithKakko()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * @Column2(type="int)eg'er")
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);

		$result = array(
		  0 =>
		  array (
		    'annotation' => 'Column2',
		    'args' =>
		    array (
		    	'type' => "int)eg'er",
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testSubNamespace()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * @Number\List
 * @Number\List(1)
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
		  0 =>
		  array (
		    'annotation' => 'Number\List',
		    'args' => null,
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		  1 =>
		  array (
		    'annotation' => 'Number\List',
		    'args' => 1,
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage Syntax error "@Number." in annotation name.:AcmeTest\Doctrine1\Entity\Product::$id:filename.php
     */
    public function testInvalidAnottationName()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
			'linenumber' => 123,
		);
        $docComment = <<<EOD
/**
 * @Number.List(1)
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    public function testDocCommentFormat()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * Comment Comment
 * {@inheritDoc}
 * @def        string       comment
 * @abc(abc)   aaa<string>  comment
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
		  0 =>
		  array (
		    'annotation' => 'def',
		    'args' => null,
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		  1 =>
		  array (
		    'annotation' => 'abc',
		    'args' => 'abc',
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testIgnoredTag()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * Comment Comment
 * @return   string       comment
 * @var(abc) aaa<string>  comment
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
		);
        $this->assertEquals($result, $annotations);
    }

    public function testInlineFormat()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * Comment Comment
 * @def @abc(abc)
 * @xyz aaa<string>  comment
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
		  0 =>
		  array (
		    'annotation' => 'def',
		    'args' => null,
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		  1 =>
		  array (
		    'annotation' => 'abc',
		    'args' => 'abc',
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		  2 =>
		  array (
		    'annotation' => 'xyz',
		    'args' => null,
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testInlineFormat2()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/** @def @abc(abc) **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
		  0 =>
		  array (
		    'annotation' => 'def',
		    'args' => null,
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		  1 =>
		  array (
		    'annotation' => 'abc',
		    'args' => 'abc',
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testCastStringForLexer()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * Comment Comment
 * @xyz(string) @abc(integer)
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
		  0 =>
		  array (
		    'annotation' => 'xyz',
		    'args' => 'string',
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		  1 =>
		  array (
		    'annotation' => 'abc',
		    'args' => 'integer',
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
				'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testListFormat()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		    'linenumber' => 123,
		);
        $docComment = <<<EOD
/**
 * @List(1,2)
 * @List({1,2})
 * @List({1})
 * @List(a=1,b=2)
 * @List({a=3,b=4})
 * @List({a=5})
 * @List(a={6,7})
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
		  0 =>
		  array (
		    'annotation' => 'List',
		    'args' =>
		    array (
		    	0 => 1,
		    	1 => 2,
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			    'filename' => 'filename.php',
			    'linenumber' => 123,
	    	),
		  ),
		  1 =>
		  array (
		    'annotation' => 'List',
		    'args' =>
		    array (
		    	0 => 1,
		    	1 => 2,
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			    'filename' => 'filename.php',
			    'linenumber' => 123,
	    	),
		  ),
		  2 =>
		  array (
		    'annotation' => 'List',
		    'args' =>
		    array (
		    	0 => 1,
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			    'filename' => 'filename.php',
			    'linenumber' => 123,
	    	),
		  ),
		  3 =>
		  array (
		    'annotation' => 'List',
		    'args' =>
		    array (
		    	'a' => 1,
		    	'b' => 2,
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			    'filename' => 'filename.php',
			    'linenumber' => 123,
	    	),
		  ),
		  4 =>
		  array (
		    'annotation' => 'List',
		    'args' =>
		    array (
		    	'a' => 3,
		    	'b' => 4,
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			    'filename' => 'filename.php',
			    'linenumber' => 123,
	    	),
		  ),
		  5 =>
		  array (
		    'annotation' => 'List',
		    'args' =>
		    array (
		    	'a' => 5,
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			    'filename' => 'filename.php',
			    'linenumber' => 123,
	    	),
		  ),
		  6 =>
		  array (
		    'annotation' => 'List',
		    'args' =>
		    array (
		    	'a' => array( 6,7 ),
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			    'filename' => 'filename.php',
			    'linenumber' => 123,
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    /**
     * @expectedException        Aneris\Annotation\Exception\DomainException
     * @expectedExceptionMessage Syntax error "/" in list.:AcmeTest\Doctrine1\Entity\Product::$id:filename.php
     */
    public function testListError()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		    'linenumber' => 123,
		);
        $docComment = <<<EOD
/**
 * @List({1/2})
  **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
    }

    public function testAnnotationList()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * @Pattern\List({
 *      @Pattern(regexp = "^(abc|xyz)+.*"),
 *      @Pattern(regexp = ".*bbb$")
 *  })
 **/
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
		  0 =>
		  array (
		    'annotation' => 'Pattern\List',
		    'args' =>
		    array (
		    	0 => array (
		    		'annotation' => 'Pattern',
		    		'args' => 
		    		array(
		    			"regexp" => "^(abc|xyz)+.*",
		    		),
				    'location' => array(
					    'target' => 'FIELD',
					    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
					    'name' => 'id',
					    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
					    'filename' => 'filename.php',
			    	),
		    	),
		    	1 => array (
		    		'annotation' => 'Pattern',
		    		'args' => 
		    		array(
		    			"regexp" => ".*bbb$",
		    		),
				    'location' => array(
					    'target' => 'FIELD',
					    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
					    'name' => 'id',
					    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
					    'filename' => 'filename.php',
			    	),
		    	),
		    ),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			    'filename' => 'filename.php',
	    	),
		  ),
		);
        $this->assertEquals($result, $annotations);
    }

    public function testAnnotationList2()
    {
    	$manager = new DummyAnnotationManager();
        $parser = new Parser($manager);
		$location = array(
			'target' => ElementType::FIELD,
			'class'  => 'AcmeTest\\Doctrine1\\Entity\\Product',
			'name'   => 'id',
			'uri'    => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			'filename' => 'filename.php',
		);
        $docComment = <<<EOD
/**
 * #@Pattern.List({
 *  @Pattern(regexp = "^(abc|xyz)+.*")
 *  @Pattern(regexp = ".*bbb$")
 * #})
 */
EOD;
        $annotations = $parser->searchAnnotation($docComment,$location);
		$result = array(
    	0 => array (
    		'annotation' => 'Pattern',
    		'args' => 
    		array(
    			"regexp" => "^(abc|xyz)+.*",
    		),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			    'filename' => 'filename.php',
	    	),
    	),
    	1 => array (
    		'annotation' => 'Pattern',
    		'args' => 
    		array(
    			"regexp" => ".*bbb$",
    		),
		    'location' => array(
			    'target' => 'FIELD',
			    'class' => 'AcmeTest\\Doctrine1\\Entity\\Product',
			    'name' => 'id',
			    'uri' => 'AcmeTest\\Doctrine1\\Entity\\Product::$id',
			    'filename' => 'filename.php',
	    	),
		),
		);
        $this->assertEquals($result, $annotations);
    }
}
