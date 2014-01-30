<?php
namespace AcmeTest\MvcRouter;

use Aneris\Mvc\Annotations\Controller;
use Aneris\Mvc\Annotations\RequestMapping;

/**
 * @Controller
 */
class TestControllerClass
{
    /**
     * @RequestMapping(value="/foo")
     */
    public function foo($context)
    {
        return;
    }
}