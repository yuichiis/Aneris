<?php
namespace AnerisTest\CacheTest;

use ArrayObject;

// Test Target Classes
use Aneris\Stdlib\Cache\CacheFactory;
use Aneris\Stdlib\Cache\CacheChain;
use Aneris\Stdlib\Cache\FileCache;
use Aneris\Stdlib\Cache\ApcCache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    protected static $backupEnableApcCache;
    protected static $backupEnableFileCache;
    protected static $backupForceFileCache;
    protected static $backupFileCachePath;

    public static function setUpBeforeClass()
    {
        CacheFactory::clearFileCache(ANERIS_TEST_CACHE);
        //$loader = Aneris\Loader\Autoloader::factory();
        //$loader->setNameSpace('AcmeTest',ANERIS_TEST_CACHE.'/AcmeTest');
        self::$backupEnableApcCache  = CacheFactory::$enableApcCache;
        self::$backupEnableFileCache = CacheFactory::$enableFileCache;
        self::$backupForceFileCache  = CacheFactory::$forceFileCache;
        self::$backupFileCachePath   = CacheFactory::$fileCachePath;
    }

    public static function tearDownAfterClass()
    {
        CacheFactory::clearFileCache(ANERIS_TEST_CACHE);
        CacheFactory::$enableApcCache  = self::$backupEnableApcCache ;
        CacheFactory::$enableFileCache = self::$backupEnableFileCache;
        CacheFactory::$forceFileCache  = self::$backupForceFileCache ;
        CacheFactory::$fileCachePath   = self::$backupFileCachePath  ;
    }

    public function setUp()
    {
    }

    public function testCacheChain()
    {
        $storage = new ArrayObject();
        $mem = new ArrayObject();
        $cache = new CacheChain($storage,$mem);

        $cache['a'] = 'A';
        $cache['b'] = 'B';

        $this->assertEquals('A',$cache['a']);
        $this->assertEquals('B',$cache['b']);

        $cache2 = new CacheChain($storage);
        $this->assertEquals('A',$cache2['a']);
        $this->assertEquals('B',$cache2['b']);

        unset($cache['a']);
        $this->assertFalse(isset($cache['a']));
        $this->assertFalse($cache->cacheExists('a'));
        unset($cache['b']);
        $this->assertFalse($cache->cacheExists('b'));

        $this->assertFalse($mem->offsetExists('a'));
        $this->assertFalse($mem->offsetExists('b'));
        $this->assertFalse($storage->offsetExists('a'));
        $this->assertFalse($storage->offsetExists('b'));

        $cache3 = new CacheChain();
        $cache3['a'] = 'A';
        $cache3['b'] = 'B';
        $this->assertEquals('A',$cache3['a']);
        $this->assertEquals('B',$cache3['b']);
        unset($cache3['a']);
        $this->assertFalse(isset($cache3['a']));
        $this->assertFalse($cache3->cacheExists('a'));
        unset($cache3['b']);
        $this->assertFalse($cache3->cacheExists('b'));
    }

    /**
     * @expectedException        PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage Offset invalid or out of range
     */
    public function testOutOfRangeException()
    {
        $storage = new ArrayObject();
        $cache = new CacheChain($storage);
        $a = $cache['a'];
    }

    public function testUnsetOutOfRange()
    {
        $storage = new ArrayObject();
        $cache = new CacheChain($storage);
        unset($cache['a']);
    }

    public function testPushToArray()
    {
        $storage = new ArrayObject();
        $cache = new CacheChain($storage);
        $cache[] = 'a';
        $this->assertEquals('a',$cache[0]);
        $this->assertEquals('a',$storage[0]);
    }

    public function testSquare()
    {
        $storage = new ArrayObject();
        $cache = new CacheChain($storage);
        if(@isset($cache['a']['b']))
            $this->assertTrue(false);
        else
            $this->assertTrue(true);
        $cache['a'] = new ArrayObject(); // array() makes error.
        $cache['a']['b'] = 'a';
        $this->assertEquals('a',$cache['a']['b']);
    }

    public function testFileStore()
    {
        $cache = new FileCache(ANERIS_TEST_CACHE.'/cache');

        $cache['a'] = 'A';
        $cache['b\\b'] = 'B';

        $this->assertEquals('A',$cache['a']);
        $this->assertEquals('B',$cache['b\\b']);

        unset($cache['a']);
        $this->assertFalse(isset($cache['a']));
        $this->assertFalse($cache->cacheExists('a'));
        unset($cache['b\\b']);

        $cache2 = new CacheChain($cache);
        $cache2['a'] = 'A';
        $cache2['b\\b'] = 'B';

        $this->assertEquals('A',$cache2['a']);
        $this->assertEquals('B',$cache2['b\\b']);

        unset($cache2['a']);
        $this->assertFalse(isset($cache2['a']));
        $this->assertFalse($cache2->cacheExists('a'));
        unset($cache2['b\\b']);

    }

    /**
     * @requires extension apc
     */
    public function testApcStore()
    {
        $cache = new ApcCache(ANERIS_TEST_CACHE.'/cache');

        $cache['a'] = 'A';
        $cache['b\\b'] = 'B';

        $this->assertEquals('A',$cache['a']);
        $this->assertEquals('B',$cache['b\\b']);

        unset($cache['a']);
        $this->assertFalse(isset($cache['a']));
        $this->assertFalse($cache->cacheExists('a'));
        unset($cache['b\\b']);

        $cache2t = new ApcCache(ANERIS_TEST_CACHE.'/cache2',1);

        $cache2t['a'] = 'A';
        $this->assertTrue($cache2t->cacheExists('a'));
        // not work
        //sleep(10);
        //$this->assertFalse($cache2t->cacheExists('a'));

        $cache2 = new CacheChain($cache);
        $cache2['a'] = 'A';
        $cache2['b\\b'] = 'B';

        $this->assertEquals('A',$cache2['a']);
        $this->assertEquals('B',$cache2['b\\b']);

        unset($cache2['a']);
        $this->assertFalse(isset($cache2['a']));
        $this->assertFalse($cache2->cacheExists('a'));
        unset($cache2['b\\b']);
    }

    /**
     * @requires extension apc
     */
    public function testSecondaryCache()
    {
        $path = ANERIS_TEST_CACHE.'/cache/chain';
        $apc = new ApcCache($path);
        $file = new FileCache($path);
        $secondary = new CacheChain($file,$apc);

        $secondary['a'] = 'A';
        $secondary['b\\b'] = 'B';

        $this->assertEquals('A',$secondary['a']);
        $this->assertEquals('B',$secondary['b\\b']);

        unset($secondary['a']);
        $this->assertFalse(isset($secondary['a']));
        $this->assertFalse($secondary->cacheExists('a'));
        unset($secondary['b\\b']);

        $primary = new CacheChain($secondary);

        $primary['a'] = 'A';
        $primary['b\\b'] = 'B';

        $this->assertEquals('A',$primary['a']);
        $this->assertEquals('B',$primary['b\\b']);

        unset($primary['a']);
        $this->assertFalse(isset($primary['a']));
        $this->assertFalse($primary->cacheExists('a'));
        unset($primary['b\\b']);

        $primary['a'] = 'A';
        $primary['b\\b'] = 'B';

        // On Other Instance 
        $path = ANERIS_TEST_CACHE.'/cache/chain';
        $apc = new ApcCache($path);
        $file = new FileCache($path);
        $secondary = new CacheChain($file,$apc);
        $primary = new CacheChain($secondary);

        $this->assertEquals('A',$primary['a']);
        $this->assertEquals('B',$primary['b\\b']);
    }

    public function testFactory()
    {
        CacheFactory::$enableApcCache = false;
        CacheFactory::$enableFileCache = true;
        CacheFactory::$forceFileCache = false;
        $cache = CacheFactory::newInstance('aa');
        $this->assertEquals('Aneris\Stdlib\Cache\FileCache',get_class($cache->getStorage()));
        $this->assertEquals('ArrayObject',get_class($cache->getCache()));

        CacheFactory::$enableApcCache = false;
        CacheFactory::$enableFileCache = true;
        CacheFactory::$forceFileCache = false;
        $cache = CacheFactory::newInstance('aa');
        $this->assertEquals('ArrayObject',get_class($cache->getCache()));
        $this->assertEquals('Aneris\Stdlib\Cache\FileCache',get_class($cache->getStorage()));

        CacheFactory::$enableApcCache = false;
        CacheFactory::$enableFileCache = false;
        CacheFactory::$forceFileCache = false;
        $cache = CacheFactory::newInstance('aa');
        $this->assertEquals('ArrayObject',get_class($cache->getCache()));
        $this->assertNull($cache->getStorage());

        CacheFactory::$enableApcCache = true;
        CacheFactory::$enableFileCache = true;
        CacheFactory::$forceFileCache = false;
    }

    /**
     * @requires extension apc
     */
    public function testFactoryWithApc()
    {
        CacheFactory::$enableApcCache = true;
        CacheFactory::$enableFileCache = true;
        CacheFactory::$forceFileCache = false;
        $cache = CacheFactory::newInstance('aa');
        $this->assertEquals('Aneris\Stdlib\Cache\ApcCache',get_class($cache->getStorage()));
        $this->assertEquals('ArrayObject',get_class($cache->getCache()));
        
        CacheFactory::$enableApcCache = true;
        CacheFactory::$enableFileCache = true;
        CacheFactory::$forceFileCache = true;
        $cache = CacheFactory::newInstance('aa');
        $this->assertEquals('Aneris\Stdlib\Cache\CacheChain',get_class($cache->getStorage()));
        $this->assertEquals('ArrayObject',get_class($cache->getCache()));
        $storage = $cache->getStorage();
        $this->assertEquals('Aneris\Stdlib\Cache\FileCache',get_class($storage->getStorage()));
        $this->assertEquals('Aneris\Stdlib\Cache\ApcCache',get_class($storage->getCache()));

        CacheFactory::$enableApcCache = true;
        CacheFactory::$enableFileCache = true;
        CacheFactory::$forceFileCache = false;
    }

    public function testFactoryCachePath()
    {
        $tmpdir = str_replace('\\', '/', sys_get_temp_dir());
        $cachedir = ANERIS_TEST_CACHE.'/cache';
        CacheFactory::$enableApcCache = false;
        CacheFactory::$enableFileCache = true;
        CacheFactory::$forceFileCache = true;
        CacheFactory::$fileCachePath = null;

        $cache = CacheFactory::newInstance('Path');
        $fileCache = $cache->getStorage();
        $this->assertEquals('Aneris\Stdlib\Cache\FileCache',get_class($fileCache));
        $this->assertEquals($tmpdir.'/Path',$fileCache->getCachePath());

        $cache = CacheFactory::newInstance('/path');
        $cache['item\test'] = 'a';

        CacheFactory::$fileCachePath = $cachedir;
        $cache = CacheFactory::newInstance('Path');
        $fileCache = $cache->getStorage();
        $this->assertEquals('Aneris\Stdlib\Cache\FileCache',get_class($fileCache));
        $this->assertEquals(str_replace('\\', '/',$cachedir).'/Path',$fileCache->getCachePath());

        $cache = CacheFactory::newInstance('Class\Name\Space\Class');
        $fileCache = $cache->getStorage();
        $this->assertEquals('Aneris\Stdlib\Cache\FileCache',get_class($fileCache));
        $this->assertEquals(str_replace('\\', '/',$cachedir).'/Class/Name/Space/Class',$fileCache->getCachePath());
    }

    public function testSetConfig()
    {
        CacheFactory::$enableApcCache = false;
        CacheFactory::$enableFileCache = false;
        CacheFactory::$forceFileCache = false;
        CacheFactory::$fileCachePath = null;

        $config = array(
            'enableApcCache'  => true,
            'enableFileCache' => true,
            'forceFileCache'  => true,
            'fileCachePath'   => '/abc/def',
        );
        CacheFactory::setConfig($config);

        $this->assertEquals(true, CacheFactory::$enableApcCache);
        $this->assertEquals(true, CacheFactory::$enableFileCache);
        $this->assertEquals(true, CacheFactory::$forceFileCache);
        $this->assertEquals('/abc/def', CacheFactory::$fileCachePath);

        $config = array(
            'enableApcCache'  => false,
            'enableFileCache' => false,
            'forceFileCache'  => false,
            'fileCachePath'   => null,
        );
        CacheFactory::setConfig($config);

        $this->assertEquals(false, CacheFactory::$enableApcCache);
        $this->assertEquals(false, CacheFactory::$enableFileCache);
        $this->assertEquals(false, CacheFactory::$forceFileCache);
        $this->assertEquals(null, CacheFactory::$fileCachePath);

        $config = array(
            'enableApcCache'  => true,
            'enableFileCache' => true,
            'forceFileCache'  => true,
            'fileCachePath'   => '/abc/def',
        );
        CacheFactory::setConfig($config);

        $this->assertEquals(true, CacheFactory::$enableApcCache);
        $this->assertEquals(true, CacheFactory::$enableFileCache);
        $this->assertEquals(true, CacheFactory::$forceFileCache);
        $this->assertEquals('/abc/def', CacheFactory::$fileCachePath);

        CacheFactory::setConfig('a');

        $this->assertEquals(true, CacheFactory::$enableApcCache);
        $this->assertEquals(true, CacheFactory::$enableFileCache);
        $this->assertEquals(true, CacheFactory::$forceFileCache);
        $this->assertEquals('/abc/def', CacheFactory::$fileCachePath);

        CacheFactory::setConfig();

        $this->assertEquals(true, CacheFactory::$enableApcCache);
        $this->assertEquals(true, CacheFactory::$enableFileCache);
        $this->assertEquals(true, CacheFactory::$forceFileCache);
        $this->assertEquals('/abc/def', CacheFactory::$fileCachePath);
    }
}
