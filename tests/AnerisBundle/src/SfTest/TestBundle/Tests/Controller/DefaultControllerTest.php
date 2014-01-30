<?php

namespace SfTest\TestBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
    	//$options['environment'] = 'dev';
    	//$options['debug'] = true;

        $client = static::createClient();

        $crawler = $client->request('GET', '/hello/Fabien');

        //print_r(get_class_methods($crawler));
        //echo get_class($crawler);
        //print_r($crawler->text());
        //print_r($crawler->extract(array('_text')));

        $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);
    }
}
