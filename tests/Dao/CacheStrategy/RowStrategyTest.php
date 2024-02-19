<?php

namespace Tests\Dao\CacheStrategy;

use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;
use Codeages\Biz\Framework\Dao\CacheStrategy\RowStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;
use Tests\Example\Dao\Impl\AnnotationExampleDaoImpl;
use Tests\IntegrationTestCase;

class RowStrategyTest extends IntegrationTestCase
{
    /**
     * @var \Redis
     */
    protected $redis;

    public function setUp(): void
    {
        parent::setUp();
        $this->redis = $this->biz['redis'];
    }

    public function testBeforeQueryHitCache()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();

        $this->redis->set("dao:{$dao->table()}:getByName:{$row['name']}", "dao:{$dao->table()}:get:{$row['id']}");
        $this->redis->set("dao:{$dao->table()}:get:{$row['id']}", $row);

        $cache = $strategy->beforeQuery($dao, 'getByName', [$row['name']]);

        $this->assertEquals($row['id'], $cache['id']);
        $this->assertEquals($row['name'], $cache['name']);
    }

    public function testBeforeQueryMissCacheRefKeyAndPrimaryKeyNotExist()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();

        $this->redis->set("dao:{$dao->table()}:getByName:{$row['name']}", "dao:{$dao->table()}:get:{$row['id']}");

        $cache = $strategy->beforeQuery($dao, 'getByName', [$row['name']]);

        $this->assertFalse($cache);
    }

    public function testBeforeQueryMissCachePrimaryKeyNotExist()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();

        $cache = $strategy->beforeQuery($dao, 'getByName', [$row['name']]);

        $this->assertFalse($cache);
    }

    public function testBeforeQueryNoCache()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $cache = $strategy->beforeQuery($dao, 'getNoCache', [1]);

        $this->assertFalse($cache);
    }

    public function testBeforeQueryOnlyForGetMethod()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();

        $this->redis->set("dao:{$dao->table()}:findByName:{$row['name']}", "dao:{$dao->table()}:get:{$row['id']}");
        $this->redis->set("dao:{$dao->table()}:get:{$row['id']}", $row);

        $cache = $strategy->beforeQuery($dao, 'findByName', [$row['name']]);

        $this->assertFalse($cache);
    }

    public function testAfterQueryWithCache()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();
        $strategy->afterQuery($dao, 'getByName', [$row['name']], $row);

        $primaryKey = $this->redis->get("dao:{$dao->table()}:getByName:{$row['name']}");
        $this->assertEquals("dao:{$dao->table()}:get:{$row['id']}", $primaryKey);

        $cache = $this->redis->get("dao:{$dao->table()}:get:{$row['id']}");
        $this->assertEquals($row['id'], $cache['id']);
        $this->assertEquals($row['name'], $cache['name']);

        $relKeys = $this->redis->get("dao:{$dao->table()}:get:{$row['id']}:rel_keys");
        $this->assertCount(1, $relKeys);
        $this->assertEquals("dao:{$dao->table()}:getByName:{$row['name']}", $relKeys[0]);
    }

    public function testAfterQueryWithCacheMultMethodCall()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();
        $strategy->afterQuery($dao, 'getByName', [$row['name']], $row);
        $strategy->afterQuery($dao, 'getByCode', [$row['code']], $row);

        $relKeys = $this->redis->get("dao:{$dao->table()}:get:{$row['id']}:rel_keys");
        $this->assertCount(2, $relKeys);
        $this->assertEquals("dao:{$dao->table()}:getByName:{$row['name']}", $relKeys[0]);
        $this->assertEquals("dao:{$dao->table()}:getByCode:{$row['code']}", $relKeys[1]);
    }

    public function testAfterQueryNoCache()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();
        $strategy->afterQuery($dao, 'getNoCache', [1], $row);

        $cache = $this->redis->get("dao:{$dao->table()}:getNoCache:1");
        $this->assertFalse($cache);
    }

    public function testAfterQueryOnlyForGetMethod()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();
        $strategy->afterQuery($dao, 'findByName', [$row['name']], $row);

        $primaryKey = $this->redis->get("dao:{$dao->table()}:findByName:{$row['name']}");

        $this->assertFalse($primaryKey);
    }

    public function testAfterUpdate()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();
        $strategy->afterQuery($dao, 'getByName', [$row['name']], $row);
        $strategy->afterQuery($dao, 'getByCode', [$row['code']], $row);

        $strategy->afterUpdate($dao, 'update', [$row['id']], $row);

        $cache = $this->redis->get("dao:{$dao->table()}:get:{$row['id']}");
        $this->assertFalse($cache);

        $cache = $this->redis->get("dao:{$dao->table()}:getByName:{$row['name']}");
        $this->assertFalse($cache);

        $cache = $this->redis->get("dao:{$dao->table()}:getByCode:{$row['code']}");
        $this->assertFalse($cache);

        $cache = $this->redis->get("dao:{$dao->table()}:get:{$row['id']}:rel_keys");
        $this->assertFalse($cache);
    }

    public function testAfterDelete()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();
        $primaryKey = $this->getPrimaryCacheKey($dao, $row['id']);
        $this->redis->set($primaryKey, $row);
        $strategy->afterDelete($dao, 'delete', [$row['id']]);

        $this->assertFalse($this->redis->get($primaryKey));
    }

    public function testAfterWave()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();
        $primaryKey = $this->getPrimaryCacheKey($dao, $row['id']);
        $this->redis->set($primaryKey, $row);
        $strategy->afterWave($dao, 'wave', [[$row['id']]], 1);

        $this->assertFalse($this->redis->get($primaryKey));
    }

    public function testFlush()
    {
        $metadataReader = new MetadataReader();
        $strategy = new RowStrategy($this->redis, $metadataReader);
        $dao = new AnnotationExampleDaoImpl($this->biz);

        $row = $this->fakeRow();
        $primaryKey = $this->getPrimaryCacheKey($dao, $row['id']);
        $this->redis->set($primaryKey, $row);
        $strategy->flush($dao);

        $this->assertFalse($this->redis->get($primaryKey));
    }

    protected function getPrimaryCacheKey(GeneralDaoInterface $dao, $id)
    {
        return "dao:{$dao->table()}:get:{$id}";
    }

    protected function fakeRow()
    {
        return [
            'id' => 1,
            'name' => 'biz_framework_name',
            'code' => 'biz_framework_code',
        ];
    }
}
