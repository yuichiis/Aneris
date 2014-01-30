<?php
namespace AnerisTest\HttpResponseTest;

// Test Target Classes
use Aneris\Http\Response;

class HttpResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testDefault()
    {
        $response = new Response();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('1.0', $response->getVersion());
        $this->assertEquals('HTTP/1.0 200 OK', $response->getStatusLine());
        $this->assertNull($response->getHeaders());
    }

    public function testNormal()
    {
        global $_SERVER;
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_NOT_FOUND);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('1.1', $response->getVersion());
        $this->assertEquals('HTTP/1.1 404 Not Found', $response->getStatusLine());
    }

    public function testHeader()
    {
        global $_SERVER;
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $response = new Response();

        $response->addHeader('Header1','Value1');
        $this->assertEquals(array('Header1'=>'Value1'), $response->getHeaders());

        $response->addHeader('Header2','Value2');
        $this->assertEquals(array('Header1'=>'Value1','Header2'=>'Value2'), $response->getHeaders());

        $response->addHeader('Header1','Value1-2');
        $this->assertEquals(array('Header1'=>array('Value1','Value1-2'),'Header2'=>'Value2'), $response->getHeaders());
    }

    /**
     * @expectedException        Aneris\Http\Exception\DomainException
     * @expectedExceptionMessage Unknown status code:1000
     */
    public function testIllegalCode()
    {
        global $_SERVER;
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $response = new Response();
        $response->setStatusCode(1000);
    }

    /**
     * @expectedException        Aneris\Http\Exception\DomainException
     * @expectedExceptionMessage Invalid type of name or value.
     */
    public function testIllegalHeaderValue()
    {
        global $_SERVER;
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $response = new Response();
        $response->addHeader('Header1',1);
    }

    /**
     * @expectedException        Aneris\Http\Exception\DomainException
     * @expectedExceptionMessage Invalid type of name or value.
     */
    public function testIllegalHeaderName()
    {
        global $_SERVER;
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $response = new Response();
        $response->addHeader(1,'aa');
    }

    public function testNumberStringHeaderValue()
    {
        global $_SERVER;
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $response = new Response();
        $response->addHeader('Header1','1');
    }

    public function testSend()
    {
        $response = new Response();
        $response->addHeader('Header1','Value1');
        $response->addHeader('Header2','Value2');
        $response->addHeader('Header1','Value1-2');
        $response->setContent('HELLO');


        $this->expectOutputString('HELLO');
        $response->send();
    }
}
