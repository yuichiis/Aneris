<?php
namespace ZFTest\ZendControllerTest;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Stdlib\Entity\EntityInterface;
use Aneris\Stdlib\Entity\EntityTrait;
use Aneris\Form\Element as Form;
use Aneris\Validator\Constraints as Assert;
use Aneris\Container\Annotations\Inject;
use Aneris\Container\Annotations\Named;

/**
 * @Form\Form(attributes={action="/app/form",method="post"})
 */
class Entity implements EntityInterface
{
    use EntityTrait;
    /**
     * @Form\Input(type="email",label="Email")
     * @Assert\Email
     */
    public $email;
    /**
     * @Form\Select(type="checkbox",label="Checkbox")
     */
    public $select;
    /**
     * @Form\Select(type="radio",label="Radio",options={red="Red",green="Green",blue="Blue"})
     */
    public $checkbox;
}

class Model
{
    use EntityTrait;
    /**
    * @Inject({@Named(value="EntityManager")})
    */
    protected $entityManager;

    public function doSomething()
    {
        if($this->entityManager==null)
            throw new \Exception('entity manager is null');
        if(get_class($this->entityManager)!='Doctrine\ORM\EntityManager')
            throw new \Exception('entity manager is '.get_class($this->entityManager));
    }
}

class TestController extends AbstractActionController
{
    protected $context;
    protected $model;

    public function onDispatch(MvcEvent $e)
    {
        if(!isset($this->context))
            $this->context = $this->getServiceLocator()->get('AnerisMvcContextFactory')->newContext();
        if(!isset($this->model))
            $this->model = $this->context->di('ZFTest\ZendControllerTest\Model');
        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        //$this->inject();

        $sm = $this->getServiceLocator()->get('AnerisServiceLocator');

        $entity = new Entity();
        $formContext = $this->context->form()->build($entity);
        $this->model->doSomething();

        return $this->redirect()->toRoute('home');
    }
}

class ZendControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public static function setUpBeforeClass()
    {
        \Aneris\Stdlib\Cache\CacheFactory::clearFileCache(__DIR__.'/app/cache');
        \Aneris\Stdlib\Cache\CacheFactory::clearCache();
    }
    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/app/config/application.config.php'
        );
        parent::setUp();
    }

    public function testIndex()
    {
        $this->dispatch('/index');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('zftest');
        $this->assertControllerName('zftest\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
    }

    public function testTestIndex()
    {
        $this->dispatch('/test');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('zftest');
        $this->assertControllerName('zftest\controller\test');
        $this->assertControllerClass('TestController');
        $this->assertMatchedRouteName('home');
    }
}