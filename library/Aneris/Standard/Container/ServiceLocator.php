<?php
namespace Aneris\Standard\Container;

interface ServiceLocator
{
    public function get($serviceName);
    public function has($serviceName);
}
