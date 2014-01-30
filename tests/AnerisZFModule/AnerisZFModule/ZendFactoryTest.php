<?php
namespace ZFTest\ZendFactoryTest;


class ServiceOnZend
{
}
class ServiceOnAneris
{
    
}

class ZendFactoryTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        \Aneris\Stdlib\Cache\CacheFactory::clearFileCache(__DIR__.'/app/cache');
        \Aneris\Stdlib\Cache\CacheFactory::clearCache();
    }

    public function setUp()
    {
        $this->app = \Zend\Mvc\Application::init(include __DIR__.'/app/config/application.config.php');
    }

    public function testValidator()
    {
        $sm = $this->app->getServiceManager();
        $validator = $sm->get('Aneris\Validator\Validator');
        $this->assertEquals('Aneris\Validator\Validator',get_class($validator));
        $this->assertEquals('Zend\Mvc\I18n\Translator',get_class($validator->getTranslator()));
        $translator = $validator->getTranslator();
        $sm->get('MvcTranslator')->setLocale('ja_JP');
        $this->assertEquals('{value}以下でなければなりません。',$translator->translate('must be less than or equal to {value}.'));
    }

    public function testForm()
    {
        $sm = $this->app->getServiceManager();
        $builder = $sm->get('Aneris\Form\FormContextBuilder');
        $this->assertEquals('Aneris\Form\FormContextBuilder',get_class($builder));
        $renderer = $sm->get('Aneris\Form\View\FormRenderer');
        $this->assertEquals('Aneris\Form\View\FormRenderer',get_class($renderer));
        $this->assertEquals('Zend\Mvc\I18n\Translator',get_class($renderer->getTranslator()));
    }

    public function testAnerisService()
    {
        $sm = $this->app->getServiceManager();
        $mm = $sm->get('Aneris\Container\ModuleManager');
        $this->assertEquals('Aneris\Container\ModuleManager',get_class($mm));
        $asm = $mm->getServiceLocator();
        $anerisServiceManager = $sm->get('AnerisServiceLocator');
        $this->assertEquals('Aneris\Container\Container',get_class($anerisServiceManager));

        $sm->setService('ServiceOnZend',new ServiceOnZend());
        $asm->setInstance('ServiceOnAneris',new ServiceOnAneris());

        $o = $asm->get('ServiceOnZend');
        $this->assertEquals('ZFTest\ZendFactoryTest\ServiceOnZend',get_class($o));

        $this->assertTrue($sm->has('ZFTest\ZendFactoryTest\ServiceOnZend'));
        $zo = $sm->get('ZFTest\ZendFactoryTest\ServiceOnZend');
        $ao = $asm->get('ZFTest\ZendFactoryTest\ServiceOnZend');
        $this->assertEquals('ZFTest\ZendFactoryTest\ServiceOnZend',get_class($zo));
        $this->assertEquals('ZFTest\ZendFactoryTest\ServiceOnZend',get_class($ao));
        $this->assertNotEquals(spl_object_hash($zo),spl_object_hash($ao));
    }

    public function testAnerisModulePath()
    {
        $sm = $this->app->getServiceManager();
        $mm = $sm->get('Aneris\Container\ModuleManager');
        $container = $sm->get('AnerisServiceLocator');
        $service1 = $container->get('ZFTest\Model\Service1');
        $this->assertEquals('ZFTest\Model\Service1',get_class($service1));
    }
}