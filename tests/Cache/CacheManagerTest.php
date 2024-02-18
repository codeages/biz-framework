<?php

namespace Tests\Cache;

use Codeages\Biz\Framework\Cache\CacheManager;
use Codeages\Biz\Framework\Context\Biz;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Redis;

class CacheManagerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var Biz
     */
    protected $biz;

    /**
     * @var Redis
     */
    protected $redis;

    public function testGetNoCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::testObj')->willReturn(false)->shouldBeCalledOnce();
        $redis->set('obj::testObj', $obj, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->get('obj', 'testObj', function () use ($obj) {
            return $obj;
        });

        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGetHasCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::testObj')->willReturn($obj)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->get('obj', 'testObj', function () use ($obj) {
            return $obj;
        });
        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGetHasNullCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::testObj')->willReturn(null)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->get('obj', 'testObj', function () use ($obj) {
            return $obj;
        });
        $this->assertNull($objCached);
    }

    public function testGetUseTtl()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::testObj')->willReturn(false)->shouldBeCalledOnce();
        $redis->set('obj::testObj', $obj, 1000)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal(), ['ttl' => 1000]);

        $result = $cache->get('obj', 'testObj', function () use ($obj) {
            return $obj;
        });
        $this->assertEquals($obj, $result);
    }

    public function testGetNoCacheAndFallbackReturnNULL()
    {
        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::testObj')->willReturn(false)->shouldBeCalledOnce();
        $redis->set('obj::testObj', null, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->get('obj', 'testObj', function () {
            return null;
        });

        $this->assertNull($objCached);
    }

    public function testGetNoCacheAndFallbackReturnZeroInt()
    {
        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::test')->willReturn(false)->shouldBeCalledOnce();
        $redis->set('obj::test', 0, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $cached = $cache->get('obj', 'test', function () {
            return 0;
        });

        $this->assertEquals(0, $cached);
    }

    public function testGetNoCacheAndFallbackReturnEmptyString()
    {
        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::test')->willReturn(false)->shouldBeCalledOnce();
        $redis->set('obj::test', '', 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $cached = $cache->get('obj', 'test', function () {
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

        $objCached = $cache->getById('obj', $obj['id'], function () use ($obj) {
            return $obj;
        });

        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGetByRefNoCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::name_obj_1')->willReturn(false)->shouldBeCalledOnce();
        $redis->set('obj::name_obj_1', $obj['id'], 3600)->shouldBeCalledOnce();
        $redis->set("obj::id_{$obj['id']}", $obj, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getByRef('obj', "name_${obj['name']}", function () use ($obj) {
            return $obj;
        });

        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGetByRefHasRefCacheButNoObjCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::name_obj_1')->willReturn(1)->shouldBeCalledOnce();
        $redis->get('obj::id_1')->willReturn(false)->shouldBeCalledOnce();
        $redis->set('obj::name_obj_1', $obj['id'], 3600)->shouldBeCalledOnce();
        $redis->set("obj::id_{$obj['id']}", $obj, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getByRef('obj', "name_${obj['name']}", function () use ($obj) {
            return $obj;
        });

        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGetByRefHasCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::name_obj_1')->willReturn(1)->shouldBeCalledOnce();
        $redis->get('obj::id_1')->willReturn($obj)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getByRef('obj', "name_${obj['name']}", function () use ($obj) {
            return $obj;
        });

        $this->assertEquals($obj['id'], $objCached['id']);
    }

    public function testGetByRefHasNullCache()
    {
        $obj = [
            'id' => 1,
            'name' => 'obj_1',
        ];

        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::name_obj_1')->willReturn(null)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getByRef('obj', "name_${obj['name']}", function () use ($obj) {
            return $obj;
        });

        $this->assertNull($objCached);
    }

    public function testGetByRefNoCacheAndFallbackReturnNULL()
    {
        $redis = $this->prophesize(Redis::class);
        $redis->get('obj::name_obj_1')->willReturn(false)->shouldBeCalledOnce();
        $redis->set('obj::name_obj_1', null, 3600)->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());

        $objCached = $cache->getByRef('obj', 'name_obj_1', function () {
            return null;
        });

        $this->assertNull($objCached);
    }

    public function testDelOneStringKey()
    {
        $key = 'testObj';
        $redis = $this->prophesize(Redis::class);
        $redis->del(['obj::testObj'])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj', 'testObj');
    }

    public function testDelStringKeyArray()
    {
        $key = 'testObj';
        $redis = $this->prophesize(Redis::class);
        $redis->del(['obj::testObj1', 'obj::testObj2'])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj', ['testObj1', 'testObj2']);
    }

    public function testDelMapWithOneIdKey()
    {
        $key = 'testObj';
        $redis = $this->prophesize(Redis::class);
        $redis->del(['obj::id_1'])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj', ['id' => 1]);
    }

    public function testDelMapWithIdKeyArray()
    {
        $key = 'testObj';
        $redis = $this->prophesize(Redis::class);
        $redis->del(['obj::id_1', 'obj::id_2'])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj', ['id' => [1, 2]]);
    }

    public function testDelMapWithOneStringKey()
    {
        $key = 'testObj';
        $redis = $this->prophesize(Redis::class);
        $redis->del(['obj::testObj1'])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj', ['key' => 'testObj1']);
    }

    public function testDelMapWithStringKeyArray()
    {
        $key = 'testObj';
        $redis = $this->prophesize(Redis::class);
        $redis->del(['obj::testObj1', 'obj::testObj2'])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj', ['key' => ['testObj1', 'testObj2']]);
    }

    public function testDelMapWithIdAndKey()
    {
        $key = 'testObj';
        $redis = $this->prophesize(Redis::class);
        $redis->del(['obj::id_1',  'obj::testObj1', 'obj::testObj2'])->shouldBeCalledOnce();

        $cache = new CacheManager($redis->reveal());
        $cache->del('obj', ['id' => 1, 'key' => ['testObj1', 'testObj2']]);
    }
}
