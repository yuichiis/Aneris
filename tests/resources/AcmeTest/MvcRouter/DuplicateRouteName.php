<?php
namespace AcmeTest\MvcRouter;

use Aneris\Mvc\Annotations\Controller;
use Aneris\Mvc\Annotations\RequestMapping;

/**
 * @Controller
 */
class DuplicateRouteName
{
    /**
     * @RequestMapping(value="/foo",name="foo")
     */
    public function foo($context)
    {
        return;
    }

    /**
     * @RequestMapping(value="/foo2",name="foo")
     */
    public function foo2($context)
    {
        return;
    }
}