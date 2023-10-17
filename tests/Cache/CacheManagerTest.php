<?php

namespace Tests\Cache;

use Codeages\Biz\Framework\Cache\CacheManager;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\RedisServiceProvider;
use PHPUnit\Framework\TestCase;
use Redis;

class CacheManagerTest extends TestCase
{
    /**
     * @var Biz
     */
    protected $biz;

    /**
     * @var Redis
     */
    protected $redis;

    public function testGet_NoCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::testObj")->willReturn(false)->shouldBeCalledOnce();
        $redis->set("obj::testObj", $obj, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->get("obj", 'testObj', function () use ($obj) {
            return $obj;
        });

        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGet_HasCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::testObj")->willReturn($obj)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->get("obj", 'testObj', function () use ($obj) {
            return $obj;
        });
        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGet_HasNullCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::testObj")->willReturn(null)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->get("obj", 'testObj', function () use ($obj) {
            return $obj;
        });
        $this->assertNull($objCached);
    }

    public function testGet_UseTtl()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::testObj")->willReturn(false)->shouldBeCalledOnce();
        $redis->set("obj::testObj", $obj, 1000)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal(), ['ttl' => 1000]);

        $cache->get("obj", 'testObj', function () use ($obj) {
            return $obj;
        });
    }

    public function testGet_NoCacheAndFallbackReturnNULL()
    {
        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::testObj")->willReturn(false)->shouldBeCalledOnce();
        $redis->set("obj::testObj", null, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->get("obj", 'testObj', function () {
            return null;
        });

        $this->assertNull($objCached);
    }

    public function testGet_NoCacheAndFallbackReturnZeroInt()
    {
        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::test")->willReturn(false)->shouldBeCalledOnce();
        $redis->set("obj::test", 0, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $cached = $cache->get("obj", 'test', function () {
            return 0;
        });

        $this->assertEquals(0, $cached);
    }

    public function testGet_NoCacheAndFallbackReturnEmptyString()
    {
        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::test")->willReturn(false)->shouldBeCalledOnce();
        $redis->set("obj::test", '', 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $cached = $cache->get("obj", 'test', function () {
            return '';
        });

        $this->assertEquals('', $cached);
    }

    public function testGetById()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::id_{$obj['id']}")->willReturn(false)->shouldBeCalledOnce();
        $redis->set("obj::id_{$obj['id']}", $obj, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getById("obj", $obj['id'], function () use ($obj) {
            return $obj;
        });

        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGetByRef_NoCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::name_obj_1")->willReturn(false)->shouldBeCalledOnce();
        $redis->set("obj::name_obj_1", $obj['id'], 3600)->shouldBeCalledOnce();
        $redis->set("obj::id_{$obj['id']}", $obj, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getByRef("obj", "name_${obj['name']}", function () use ($obj) {
            return $obj;
        });

        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGetByRef_HasRefCacheButNoObjCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::name_obj_1")->willReturn(1)->shouldBeCalledOnce();
        $redis->get("obj::id_1")->willReturn(false)->shouldBeCalledOnce();
        $redis->set("obj::name_obj_1", $obj['id'], 3600)->shouldBeCalledOnce();
        $redis->set("obj::id_{$obj['id']}", $obj, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getByRef("obj", "name_${obj['name']}", function () use ($obj) {
            return $obj;
        });

        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGetByRef_HasCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::name_obj_1")->willReturn(1)->shouldBeCalledOnce();
        $redis->get("obj::id_1")->willReturn($obj)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getByRef("obj", "name_${obj['name']}", function () use ($obj) {
            return $obj;
        });

        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGetByRef_HasNullCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::name_obj_1")->willReturn(null)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getByRef("obj", "name_${obj['name']}", function () use ($obj) {
            return $obj;
        });

        $this->assertNull($objCached);
    }

    public function testGetByRef_NoCacheAndFallbackReturnNULL()
    {

        $redis = $this->prophesize(Redis::class);
        $redis->get("obj::name_obj_1")->willReturn(false)->shouldBeCalledOnce();
        $redis->set("obj::name_obj_1", null, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getByRef("obj", "name_obj_1", function () {
            return null;
        });

        $this->assertNull($objCached);
    }

    public function testDel_OneStringKey()
    {
        $key = "testObj";
        $redis = $this->prophesize(Redis::class);
        $redis->del(["obj::testObj"])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj',  'testObj');
    }

    public function testDel_StringKeyArray()
    {
        $key = "testObj";
        $redis = $this->prophesize(Redis::class);
        $redis->del(["obj::testObj1", "obj::testObj2"])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj',  ['testObj1', 'testObj2']);
    }

    public function testDel_MapWithOneIdKey()
    {
        $key = "testObj";
        $redis = $this->prophesize(Redis::class);
        $redis->del(["obj::id_1"])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj',  ['id' => 1]);
    }

    public function testDel_MapWithIdKeyArray()
    {
        $key = "testObj";
        $redis = $this->prophesize(Redis::class);
        $redis->del(["obj::id_1", "obj::id_2"])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj',  ['id' => [1, 2]]);
    }

    public function testDel_MapWithOneStringKey()
    {
        $key = "testObj";
        $redis = $this->prophesize(Redis::class);
        $redis->del(["obj::testObj1"])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj',  ['key' => 'testObj1']);
    }

    public function testDel_MapWithStringKeyArray()
    {
        $key = "testObj";
        $redis = $this->prophesize(Redis::class);
        $redis->del(["obj::testObj1", "obj::testObj2"])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj',  ['key' => ['testObj1', 'testObj2']]);
    }

    public function testDel_MapWithIdAndKey()
    {
        $key = "testObj";
        $redis = $this->prophesize(Redis::class);
        $redis->del(["obj::id_1",  "obj::testObj1", "obj::testObj2"])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj', ['id' => 1, 'key' => ['testObj1', 'testObj2']]);
    }
}
