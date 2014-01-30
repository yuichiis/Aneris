<?php
namespace AnerisTest\HttpRequestTest;

// Test Target Classes
use Aneris\Http\Request;

class HttpRequestTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testPathRoot()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/web.php';
        $_SERVER['REQUEST_URI'] = '/web.php';
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testPathRoot2()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/';
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/abc', $request->getRootPath().'/abc');
    }

    public function testPathRoot3()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/web.php';
        $_SERVER['REQUEST_URI'] = '/web.php/';
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testPathNoQuery()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/web.php';
        $_SERVER['REQUEST_URI'] = '/web.php/path';
        $request = new Request();
        $this->assertEquals('/path', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testPathNoQuery2()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/path';
        $request = new Request();
        $this->assertEquals('/path', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/abc', $request->getRootPath().'/abc');
    }

    public function testPathNoQuery3()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/web.php';
        $_SERVER['REQUEST_URI'] = '/web.php/path/';
        $request = new Request();
        $this->assertEquals('/path/', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testPathNoQuery4()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/path/';
        $request = new Request();
        $this->assertEquals('/path/', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/abc', $request->getRootPath().'/abc');
    }

    public function testPathRootWithQuery()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/web.php';
        $_SERVER['REQUEST_URI'] = '/web.php?q=1';
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testPathRootWithQuery2()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/?q=1';
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/abc', $request->getRootPath().'/abc');
    }

    public function testPathRootWithQuery3()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/web.php';
        $_SERVER['REQUEST_URI'] = '/web.php/?q=1';
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testPathWithQuery()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/web.php';
        $_SERVER['REQUEST_URI'] = '/web.php/path?q=1';
        $request = new Request();
        $this->assertEquals('/path', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testPathWithQuery2()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/path?q=1';
        $request = new Request();
        $this->assertEquals('/path', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/abc', $request->getRootPath().'/abc');
    }

    public function testSubPathWithQuery()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/web.php';
        $_SERVER['REQUEST_URI'] = '/web.php/path/sub?q=1';
        $request = new Request();
        $this->assertEquals('/path/sub', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testSubPathWithQuery2()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/path/sub?q=1';
        $request = new Request();
        $this->assertEquals('/path/sub', $request->getPath());
        $this->assertEquals('/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/abc', $request->getRootPath().'/abc');
    }

    public function testSubDirPathRoot()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/web.php';
        $_SERVER['REQUEST_URI'] = '/sub/web.php';
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('/sub/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/sub/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testSubDirPathRoot2()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';
        $_SERVER['REQUEST_URI'] = '/sub';
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('/sub/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/sub/abc', $request->getRootPath().'/abc');
    }

    public function testSubDirPathRoot3()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/web.php';
        $_SERVER['REQUEST_URI'] = '/sub/web.php/';
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('/sub/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/sub/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testSubDirPathRoot4()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';
        $_SERVER['REQUEST_URI'] = '/sub/';
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('/sub/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/sub/abc', $request->getRootPath().'/abc');
    }

    public function testSubDirPathNoQuery()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/web.php';
        $_SERVER['REQUEST_URI'] = '/sub/web.php/path';
        $request = new Request();
        $this->assertEquals('/path', $request->getPath());
        $this->assertEquals('/sub/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/sub/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testSubDirPathNoQuery2()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';
        $_SERVER['REQUEST_URI'] = '/sub/path';
        $request = new Request();
        $this->assertEquals('/path', $request->getPath());
        $this->assertEquals('/sub/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/sub/abc', $request->getRootPath().'/abc');
    }

    public function testSubDirPathNoQuery3()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/web.php';
        $_SERVER['REQUEST_URI'] = '/sub/web.php/path/';
        $request = new Request();
        $this->assertEquals('/path/', $request->getPath());
        $this->assertEquals('/sub/img/foo.jpg', $request->getPathPrefix().'/img/foo.jpg');
        $this->assertEquals('/sub/web.php/abc', $request->getRootPath().'/abc');
    }

    public function testSubDirPathNoQuery4()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';
        $_SERVER['REQUEST_URI'] = '/sub/path/';
        $request = new Request();
        $this->assertEquals('/sub', $request->getPathPrefix());
        $this->assertEquals('/path/', $request->getPath());
    }

    public function testSubDirPathWithQuery()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/web.php';
        $_SERVER['REQUEST_URI'] = '/sub/web.php/path?q=1';
        $request = new Request();
        $this->assertEquals('/sub', $request->getPathPrefix());
        $this->assertEquals('/path', $request->getPath());
    }

    public function testSubDirPathWithQuery2()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';
        $_SERVER['REQUEST_URI'] = '/sub/path?q=1';
        $request = new Request();
        $this->assertEquals('/sub', $request->getPathPrefix());
        $this->assertEquals('/path', $request->getPath());
    }

    public function testSubDirSubPathWithQuery()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/web.php';
        $_SERVER['REQUEST_URI'] = '/sub/web.php/path/sub?q=1';
        $request = new Request();
        $this->assertEquals('/sub', $request->getPathPrefix());
        $this->assertEquals('/path/sub', $request->getPath());
    }

    public function testSubDirSubPathWithQuery2()
    {
        global $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '/sub/index.php';
        $_SERVER['REQUEST_URI'] = '/sub/path/sub?q=1';
        $request = new Request();
        $this->assertEquals('/sub', $request->getPathPrefix());
        $this->assertEquals('/path/sub', $request->getPath());
    }

    public function testPostNone()
    {
        $request = new Request();
        $this->assertEquals(null, $request->getPost('q'));
        $this->assertEquals('a', $request->getPost('q','a'));
    }

    public function testPostNoneAndDefault()
    {
        global $_POST;
        $_POST['q'] = 'x';

        $request = new Request();
        $this->assertEquals('x', $request->getPost('q'));
        $this->assertEquals('x', $request->getPost('q','a'));
    }

    public function testQueryNone()
    {
        $request = new Request();
        $this->assertEquals(null, $request->getQuery('q'));
        $this->assertEquals('a', $request->getQuery('q','a'));
    }

    public function testQueryNoneAndDefault()
    {
        global $_GET;
        $_GET['q'] = 'x';

        $request = new Request();
        $this->assertEquals('x', $request->getQuery('q'));
        $this->assertEquals('x', $request->getQuery('q','a'));
    }
}
