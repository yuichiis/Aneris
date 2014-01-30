<?php
namespace AcmeTest\MvcRouter\Controller;

use Aneris\Mvc\Annotations\Controller;
use Aneris\Mvc\Annotations\RequestMapping;

/**
 * @Controller
 */
class TestControllerClass1
{
    /**
     * @RequestMapping(value="/foo")
     */
    public function foo($context)
    {
        return;
    }
}