<?php
namespace AnerisTest\AnnotationManagerTest\Foo {
	use Aneris\Stdlib\ListCollection;
	class MyClass
	{
		public function test()
		{
			return new ListCollection();
		}
	}
	trait TestTrait
	{
		public function test2()
		{
			return new ListCollection();
		}
	}
	class MyClass2
	{
		public function boo()
		{
			return;
		}
	}
}

namespace AnerisTest\AnnotationManagerTest\Bar {
	use Aneris\Stdlib\PriorityQueue as ListCollection;
	class MyClass
	{
        use \Aneris2AnnotationManagerTest\Foo\TestTrait;
		public function test()
		{
			return new ListCollection();
		}
	}
}

namespace {
	use stdClass as ListCollection, Aneris\TestList;
	include __DIR__.'/../../../development/init_autoloader.php';
	$o = new Aneris2AnnotationManagerTest\Foo\MyClass();
	echo get_class($o->test())."\n";
	$o = new Aneris2AnnotationManagerTest\Bar\MyClass();
	echo get_class($o->test())."\n";
	$o = new ListCollection();
	echo get_class($o)."\n";
	$o = new Aneris2AnnotationManagerTest\Bar\Myclass();
	echo get_class($o->test2())."\n";

	$parser = new Aneris\Annotation\NameSpaceExtractor(__FILE__);
	$imports = $parser->getAllImports();
	print_r($imports);
}