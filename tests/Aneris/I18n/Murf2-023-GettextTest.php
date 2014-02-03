<?php
namespace AnerisTest\GettextTest;

use Aneris\Container\ModuleManager;

// Test Target Classes
use Aneris\Stdlib\I18n\Gettext;

class GettextTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    	//Aneris\Stdlib\I18n\Gettext::initialize();
    }

    public static function tearDownAfterClass()
    {
    }

    public function testHeader()
    {
        $fd = fopen(ANERIS_TEST_RESOURCES.'/php/messages/en_US/LC_MESSAGES/domain1.mo','rb');
        $gettext = new Gettext();
        $header = $gettext->readHeader($fd);
        $result = $gettext->buildTextDomain($fd,$header);
        fclose($fd);
        $this->assertEquals(2,count($result['text']));
        $this->assertEquals('gettext translation test textdomain:domain1 locale:en_US',
            $result['text']['{aneris.test.gettext.domain1.messages}']);
        $this->assertEquals('Welcome to My PHP Application',
            $result['text']['{aneris.test.phptest.gettext.messages}']);
        $this->assertTrue(isset($result['header']));
    }

    public function testGettext()
    {
        $gettext = Gettext::factory();
        $this->assertEquals('abc',$gettext->getText('abc'));

        $gettext->bindTextDomain('domain1',ANERIS_TEST_RESOURCES.'/php/messages');
        $this->assertEquals('gettext translation test textdomain:domain1 locale:en_US',
            $gettext->getText('{aneris.test.gettext.domain1.messages}','domain1','en_US'));
        $this->assertEquals('Welcome to My PHP Application',
            $gettext->getText('{aneris.test.phptest.gettext.messages}','domain1','en_US'));
    }
}