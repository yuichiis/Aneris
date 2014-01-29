<?php
namespace Aneris\Container;

interface ServiceLocatorInterface
{
    public function get($serviceName);
    public function has($serviceName);
}
