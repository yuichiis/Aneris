<?php
namespace AnerisTest\TranslatorTest;

use Aneris\Container\ModuleManager;

// Test Target Classes
use Aneris\Stdlib\I18n\Translator;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    	//Aneris\Stdlib\I18n\Translator::initialize();
    }

    public static function tearDownAfterClass()
    {
    }

    public function testNormal()
    {
    	$translator = new Translator();
    	$translator->bindTextDomain('domain1' ,ANERIS_TEST_RESOURCES.'/php/messages');
        $translator->setLocale('en_US');
        $translator->setTextDomain('domain1');
    	$result = $translator->translate("{aneris.test.gettext.domain1.messages}");
		$this->assertEquals('gettext translation test textdomain:domain1 locale:en_US',$result);

    	$result = $translator->translate("{aneris.test.gettext.domain1.messages}",null,'ja_JP');
		$this->assertEquals('gettext translation test textdomain:domain1 locale:ja_JP',$result);

    	$result = $translator->translate("{aneris.test.gettext.domain1.messages}");
		$this->assertEquals('gettext translation test textdomain:domain1 locale:en_US',$result);

    	$result = $translator->translate("{aneris.test.gettext.domain1.messages}",'hogehoge');
		$this->assertEquals('{aneris.test.gettext.domain1.messages}',$result);
    }

    public function testMultiSession()
    {
    	$translator1 = new Translator();
    	$translator1->bindTextDomain('domain1' ,ANERIS_TEST_RESOURCES.'/php/messages','en_US');
        $translator1->setLocale('en_US');
        $translator1->setTextDomain('domain1');

    	$translator2 = new Translator();
    	$translator2->bindTextDomain('domain2' ,ANERIS_TEST_RESOURCES.'/php/messages','en_US');
        $translator2->setLocale('en_US');
        $translator2->setTextDomain('domain2');

    	$result = $translator1->translate("{aneris.test.gettext.domain1.messages}");
		$this->assertEquals('gettext translation test textdomain:domain1 locale:en_US',$result);

    	$result = $translator2->translate("{aneris.test.gettext.domain1.messages}");
		$this->assertEquals('gettext translation test textdomain:domain2 locale:en_US',$result);

    	$result = $translator2->translate("{aneris.test.gettext.domain1.messages}",'domain1');
		$this->assertEquals('gettext translation test textdomain:domain1 locale:en_US',$result);

    	$result = $translator2->translate("{aneris.test.gettext.domain1.messages}");
		$this->assertEquals('gettext translation test textdomain:domain2 locale:en_US',$result);

    	$result = $translator1->translate("{aneris.test.gettext.domain1.messages}");
		$this->assertEquals('gettext translation test textdomain:domain1 locale:en_US',$result);
    }

    public function testOnModule()
    {
        $config = array(
            'module_manager' => array(
                'modules' => array(
                    'Aneris\Stdlib\I18n\Module' => true,
                ),
            ),
            'translator' => array(
                'translation_file_patterns' => array(
                    array(
                        'type'        => 'Gettext',
                        'base_dir'    => ANERIS_TEST_RESOURCES.'/php/messages',
                        'text_domain' => 'domain3',
                    ),
                ),
            ),
        );
        $moduleManager = new ModuleManager($config);
        $sm = $moduleManager->getServiceLocator();
        $translator = $sm->get('Aneris\Stdlib\I18n\Translator');

        $result = $translator->translate("{aneris.test.gettext.domain1.messages}",'domain3','en_US');
        $this->assertEquals('gettext translation test textdomain:domain3 locale:en_US',$result);
    }
}