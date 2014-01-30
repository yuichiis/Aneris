<?php

namespace SfTest\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
    	$aneris = $this->get('aneris.container.module_manager');
    	if('Aneris\Container\ModuleManager' != get_class($aneris))
    		throw new \Exception("Not ModuleManager");
    	$service_locator = $this->get('aneris.container.service_locator');
    	if('Aneris\Container\Container' != get_class($service_locator))
    		throw new \Exception("Not ServiceLocator");
    	if(spl_object_hash($service_locator)!=spl_object_hash($aneris->getServiceLocator()))
    		throw new \Exception("Not Match ServiceLocator Object Hash");
    	
        return $this->render('SfTestTestBundle:Default:index.html.twig', array('name' => $name));
    }
}
