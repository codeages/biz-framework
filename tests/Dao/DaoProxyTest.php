<?php

namespace Tests\Dao;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;
use Codeages\Biz\Framework\Dao\ArrayStorage;
use Codeages\Biz\Framework\Dao\DaoProxy;
use Codeages\Biz\Framework\Dao\FieldSerializer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class DaoProxyTest extends TestCase
{
    use ProphecyTrait;

    public function testGetHitCache()
    {
        $expected = ['id' => 1, 'name' => 'test'];
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'get');
        $row = $proxy->get($expected['id']);

        $this->assertEquals($expected['id'], $row['id']);
    }

    public function testGetMissCache()
    {
        $expected = ['id' => 1, 'name' => 'test'];
        $proxy = $this->mockDaoProxyWithMissCache($expected, 'get');
        $row = $proxy->get($expected['id']);

        $this->assertEquals($expected, $row);
    }

    public function testGetNoCache()
    {
        $expected = ['id' => 1, 'name' => 'test'];
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'get');
        $row = $proxy->get($expected['id']);
        $this->assertEquals($expected, $row);
    }

    public function testGetMultiCallHitArrayStorageCache()
    {
        $storage = new ArrayStorage();
        $expected = ['id' => 1, 'name' => 'test'];
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'get', $storage);
        $row = $proxy->get($expected['id']);
        $this->assertEquals($expected, $row);

        $proxy = $this->mockDaoProxyWithNoCacheAndNoRealCall($storage);
        $row = $proxy->get($expected['id']);
        $this->assertEquals($expected, $row);
    }

    public function testGetLock()
    {
        $expected = ['id' => 1, 'name' => 'test'];

        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn([]);
        $dao->get(Argument::cetera())->willReturn($expected);

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.enabled'] = true;

        $proxy = new DaoProxy($biz, $dao->reveal(), new MetadataReader(), $serializer);

        $row = $proxy->get($expected['id'], ['lock' => true]);

        $this->assertEquals($expected['id'], $row['id']);
    }

    /**
     * @group current
     */
    public function testFindHitCache()
    {
        $expected = [
            ['id' => 1, 'name' => 'test 1'],
            ['id' => 2, 'name' => 'test 2'],
        ];
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'find');

        $rows = $proxy->find();

        $this->assertEquals($expected, $rows);
    }

    public function testSearchHitCache()
    {
        $expected = [
            ['id' => 1, 'name' => 'test 1'],
            ['id' => 2, 'name' => 'test 2'],
        ];
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'search');
        $rows = $proxy->search();

        $this->assertEquals($expected, $rows);
    }

    public function testSearchMissCache()
    {
        $expected = [
            ['id' => 1, 'name' => 'test 1'],
            ['id' => 2, 'name' => 'test 2'],
        ];
        $proxy = $this->mockDaoProxyWithMissCache($expected, 'search');
        $row = $proxy->search([], [], 0, 100);

        $this->assertEquals($expected, $row);
    }

    public function testSearchNoCache()
    {
        $expected = ['id' => 1, 'name' => 'test'];
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'search');
        $rows = $proxy->search([], [], 0, 1);
        $this->assertEquals($expected, $rows);
    }

    public function testCountHitCache()
    {
        $expected = 2;
        $proxy = $this->mockDaoProxyWithHitCache($expected, 'count');
        $count = $proxy->count();

        $this->assertEquals($expected, $count);
    }

    public function testCountMissCache()
    {
        $expected = 2;
        $proxy = $this->mockDaoProxyWithMissCache($expected, 'count');
        $count = $proxy->count([]);

        $this->assertEquals($expected, $count);
    }

    public function testCountNoCache()
    {
        $expected = 1;
        $proxy = $this->mockDaoProxyWithNoCache($expected, 'count');
        $count = $proxy->count([]);

        $this->assertEquals($expected, $count);
    }

    private function mockDaoProxyWithHitCache($expected, $proxyMethod, $arrayStorage = null)
    {
        $strategy = $this->prophesize('Codeages\Biz\Framework\Dao\CacheStrategy');
        $strategy->beforeQuery(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::any(),
            Argument::type('array')
        )->willReturn($expected);

        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.enabled'] = true;
        $biz['dao.cache.strategy.default'] = $strategy->reveal();

        return new DaoProxy($biz, $dao->reveal(), new MetadataReader(), $serializer, $arrayStorage);
    }

    private function mockDaoProxyWithMissCache($expected, $proxyMethod, $arrayStorage = null)
    {
        $strategy = $this->prophesize('Codeages\Biz\Framework\Dao\CacheStrategy');
        $strategy->beforeQuery(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::type('string'),
            Argument::type('array')
        )->willReturn(false);
        $strategy->afterQuery(
            Argument::type('Codeages\Biz\Framework\Dao\GeneralDaoInterface'),
            Argument::type('string'),
            Argument::type('array'),
            Argument::any()
        )->willReturn(null);

        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn([]);
        $dao->$proxyMethod(Argument::cetera())->willReturn($expected);

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.enabled'] = true;
        $biz['dao.cache.strategy.default'] = $strategy->reveal();

        return new DaoProxy($biz, $dao->reveal(), new MetadataReader(), $serializer, $arrayStorage);
    }

    private function mockDaoProxyWithNoCache($expected, $proxyMethod, $arrayStorage = null)
    {
        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn([]);
        $dao->table()->willReturn('example');
        $dao->$proxyMethod(Argument::cetera())->willReturn($expected);

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.enabled'] = false;

        return new DaoProxy($biz, $dao->reveal(), new MetadataReader(), $serializer, $arrayStorage);
    }

    private function mockDaoProxyWithNoCacheAndNoRealCall($arrayStorage = null)
    {
        $dao = $this->prophesize('Codeages\Biz\Framework\Dao\GeneralDaoInterface');
        $dao->declares()->willReturn([]);
        $dao->table()->willReturn('example');

        $serializer = new FieldSerializer();

        $biz = new Biz();
        $biz['dao.cache.enabled'] = false;

        return new DaoProxy($biz, $dao->reveal(), new MetadataReader(), $serializer, $arrayStorage);
    }
}
