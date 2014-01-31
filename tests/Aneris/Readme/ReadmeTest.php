<?php
namespace AnerisTest\ReadmeTest;

use Michelf\Markdown;

class ReadmeTest extends \PHPUnit_Framework_TestCase
{
	public function testReadme()
	{
		$makedown = new Markdown();

		$source = file_get_contents(ANERIS_REPOSITORY_ROOT.'/README.md');
		$html = $makedown->transform($source);
		file_put_contents(ANERIS_TEST_DATA.'/readme.html', $html);
		$this->assertTrue(true);
	}
}