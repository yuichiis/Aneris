<?php

namespace SfTest\TestBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlUserLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);
        $configValues = Yaml::parse($path);

        // ... handle the config values

        // maybe import some other resource:

        // $this->import('extra_users.yml');
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}

class ServiceTest extends TestCase
{
    public function setup()
    {
        include_once $_SERVER['KERNEL_DIR'].'AppKernel.php';
        $env = 'test';
        $debug = true;
        $this->kernel = new \AppKernel($env,$debug);
        $this->kernel->boot();
    }

    public function testConfigComponent()
    {
        $configDirectories = array($_SERVER['KERNEL_DIR'].'config');
        $locator = new \Symfony\Component\Config\FileLocator($configDirectories);
        //$loaderResolver = new \Symfony\Component\Config\Loader\LoaderResolver(
        //    array(new YamlUserLoader($locator)));
        //$delegatingLoader = new \Symfony\Component\Config\Loader\DelegatingLoader($loaderResolver);
        //$delegatingLoader->load('routing.yml');
        $loader = new YamlUserLoader($locator);
        $loader->load('routing.yml');
    }

	public function testModuleManager()
	{
		$container = $this->kernel->getContainer();
        $this->assertTrue($container->has('aneris.container.module_manager'));
        $aneris = $container->get('aneris.container.module_manager');
        $this->assertEquals('Aneris\Container\ModuleManager',get_class($aneris));
        $config = $aneris->getConfig();
        $this->assertTrue(isset($config['doctrine']));
	}

    public function testServiceLocator()
    {
        $container = $this->kernel->getContainer();
        $this->assertTrue($container->has('aneris.container.service_locator'));
        $aneris = $container->get('aneris.container.service_locator');
        $this->assertEquals('Aneris\Container\Container',get_class($aneris));
        $config = $aneris->get('config');
        $this->assertTrue(isset($config['doctrine']));
        $twig = $aneris->get('twig');
    }

    public function testValidator()
    {
        $container = $this->kernel->getContainer();
        $this->assertTrue($container->has('aneris.validator.validator.service'));
        $validator = $container->get('aneris.validator.validator.service');
        $this->assertEquals('Aneris\Validator\Validator',get_class($validator));
        $this->assertEquals('en', $validator->getTranslator()->getLocale());
    }

    public function testForm()
    {
        $container = $this->kernel->getContainer();
        $this->assertTrue($container->has('aneris.form.context_builder.service'));
        $builder = $container->get('aneris.form.context_builder.service');
        $this->assertEquals('Aneris\Form\FormContextBuilder',get_class($builder));
        $this->assertTrue($container->has('aneris.form.renderer.service'));
        $renderer = $container->get('aneris.form.renderer.service');
        $this->assertEquals('Aneris\Form\View\FormRenderer',get_class($renderer));
        $this->assertEquals(
            'Aneris\Bundle\AnerisBundle\DependencyInjection\TranslatorProxy',
            get_class($renderer->getTranslator()));
        $this->assertEquals('en', $renderer->getTranslator()->getLocale());
        $theme = $renderer->getTheme();
        $this->assertTrue(isset($theme['field']));
        $this->assertTrue(isset($theme['label']));
    }
}
