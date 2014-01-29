<?php
namespace Aneris\Container;

interface ProxyManagerInterface
{
    public function newProxy(Container $container,ComponentDefinition $component);
}
