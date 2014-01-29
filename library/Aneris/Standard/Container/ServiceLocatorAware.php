<?php
namespace Aneris\Standard\Container;

interface ServiceLocatorAware
{
    public function setServiceLocator(ServiceLocator $serviceLocator);
}
