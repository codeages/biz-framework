<?php
namespace Tests\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy\RowStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;
use TestProject\Biz\Example\Dao\Impl\ExampleWithCacheStrategyAnnotationDaoImpl;
use Tests\BaseTestCase;

class RowStrategyTest extends BaseTestCase
{
    /**
     * @var \Redis
     */
    protected $redis;

    protected $biz;

    public function setUp()
    {
        $this->redis = $this->createRedis();
        $this->redis->flushDB();
        $this->biz = $this->createBiz();
    }

    public function testBeforeQuery_HitCache()
    {
        $strategy = new RowStrategy($this->redis);
        $dao = new ExampleWithCacheStrategyAnnotationDaoImpl($this->biz);

        $row = $this->fakeRow();

        $this->redis->set("dao:{$dao->table()}:getByName:{$row['name']}", "dao:{$dao->table()}:get:{$row['id']}");
        $this->redis->set("dao:{$dao->table()}:get:{$row['id']}", $row);

        $cache = $strategy->beforeQuery($dao, 'getByName', [$row['name']]);

        $this->assertEquals($row['id'], $cache['id']);
        $this->assertEquals($row['name'], $cache['name']);
    }

    public function testBeforeQuery_MissCache_RefKeyAndPrimaryKeyNotExist()
    {
        $strategy = new RowStrategy($this->redis);
        $dao = new ExampleWithCacheStrategyAnnotationDaoImpl($this->biz);

        $row = $this->fakeRow();

        $this->redis->set("dao:{$dao->table()}:getByName:{$row['name']}", "dao:{$dao->table()}:get:{$row['id']}");

        $cache = $strategy->beforeQuery($dao, 'getByName', [$row['name']]);

        $this->assertFalse($cache);
    }

    public function testBeforeQuery_MissCache_PrimaryKeyNotExist()
    {
        $strategy = new RowStrategy($this->redis);
        $dao = new ExampleWithCacheStrategyAnnotationDaoImpl($this->biz);

        $row = $this->fakeRow();

        $cache = $strategy->beforeQuery($dao, 'getByName', [$row['name']]);

        $this->assertFalse($cache);
    }

    public function testBeforeQuery_NoCache()
    {
        $strategy = new RowStrategy($this->redis);
        $dao = new ExampleWithCacheStrategyAnnotationDaoImpl($this->biz);

        $cache = $strategy->beforeQuery($dao, 'getNoCache', [1]);

        $this->assertFalse($cache);
    }

    public function testBeforeQuery_OnlyForGetMethod()
    {
        $strategy = new RowStrategy($this->redis);
        $dao = new ExampleWithCacheStrategyAnnotationDaoImpl($this->biz);

        $row = $this->fakeRow();

        $this->redis->set("dao:{$dao->table()}:findByName:{$row['name']}", "dao:{$dao->table()}:get:{$row['id']}");
        $this->redis->set("dao:{$dao->table()}:get:{$row['id']}", $row);

        $cache = $strategy->beforeQuery($dao, 'findByName', [$row['name']]);

        $this->assertFalse($cache);
    }

    public function testAfterQuery_WithCache()
    {
        $strategy = new RowStrategy($this->redis);
        $dao = new ExampleWithCacheStrategyAnnotationDaoImpl($this->biz);

        $row = $this->fakeRow();
        $strategy->afterQuery($dao, 'getByName', [$row['name']], $row);

        $primaryKey = $this->redis->get("dao:{$dao->table()}:getByName:{$row['name']}");
        $this->assertEquals("dao:{$dao->table()}:get:{$row['id']}", $primaryKey);

        $cache = $this->redis->get("dao:{$dao->table()}:get:{$row['id']}");
        $this->assertEquals($row['id'], $cache['id']);
        $this->assertEquals($row['name'], $cache['name']);
    }

    public function testAfterQuery_NoCache()
    {
        $strategy = new RowStrategy($this->redis);
        $dao = new ExampleWithCacheStrategyAnnotationDaoImpl($this->biz);

        $row = $this->fakeRow();
        $strategy->afterQuery($dao, 'getNoCache', [1], $row);

        $cache = $this->redis->get("dao:{$dao->table()}:getNoCache:1");
        $this->assertFalse($cache);
    }

    public function testAfterQuery_OnlyForGetMethod()
    {
        $strategy = new RowStrategy($this->redis);
        $dao = new ExampleWithCacheStrategyAnnotationDaoImpl($this->biz);

        $row = $this->fakeRow();
        $strategy->afterQuery($dao, 'findByName', [$row['name']], $row);

        $primaryKey = $this->redis->get("dao:{$dao->table()}:findByName:{$row['name']}");

        $this->assertFalse($primaryKey);
    }

    public function testAfterDelete()
    {
        $strategy = new RowStrategy($this->redis);
        $dao = new ExampleWithCacheStrategyAnnotationDaoImpl($this->biz);

        $row = $this->fakeRow();
        $primaryKey = $this->getPrimaryCacheKey($dao, $row['id']);
        $this->redis->set($primaryKey, $row);
        $strategy->afterDelete($dao, 'delete', [$row['id']]);

        $this->assertFalse($this->redis->get($primaryKey));
    }

    public function testAfterWave()
    {
        $strategy = new RowStrategy($this->redis);
        $dao = new ExampleWithCacheStrategyAnnotationDaoImpl($this->biz);

        $row = $this->fakeRow();
        $primaryKey = $this->getPrimaryCacheKey($dao, $row['id']);
        $this->redis->set($primaryKey, $row);
        $strategy->afterWave($dao, 'wave', [[$row['id']]], 1);

        $this->assertFalse($this->redis->get($primaryKey));
    }

    protected function getPrimaryCacheKey(GeneralDaoInterface $dao, $id)
    {
        return "dao:{$dao->table()}:get:{$id}";
    }

    protected function fakeRow()
    {
        return array(
            'id' => 1,
            'name' => 'lilei'
        );
    }
}