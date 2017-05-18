<?php
namespace Tests\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy\RowStrategy;
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

    public function testBeforeQuery_MissCache()
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

    protected function fakeRow()
    {
        return array(
            'id' => 1,
            'name' => 'lilei'
        );
    }
}