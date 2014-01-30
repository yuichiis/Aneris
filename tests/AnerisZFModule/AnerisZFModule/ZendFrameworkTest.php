<?php
namespace ZFTest\ZendFrameworkTest;

class ServiceOnZend
{
}

class ZendFrameworkTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->app = \Zend\Mvc\Application::init(include __DIR__.'/app/config/application.config.php');
    }

    public function testValidator()
    {
    	$sm = $this->app->getServiceManager();
    	$this->assertTrue($sm->has('ZFTest\ZendFrameworkTest\ServiceOnZend'));
    	$o = $sm->get('ZFTest\ZendFrameworkTest\ServiceOnZend');
    }
}
