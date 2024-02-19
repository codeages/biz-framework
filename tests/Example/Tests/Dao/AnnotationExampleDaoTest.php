<?php

namespace Tests\Example\Tests\Dao;

use Doctrine\Common\Collections\ArrayCollection;
use Tests\Example\Dao\ExampleDao;
use Tests\IntegrationTestCase;

class AnnotationExampleDaoTest extends IntegrationTestCase
{
    /**
     * @var ExampleDao
     */
    protected $dao;

    /**
     * @var ArrayCollection
     */
    protected $rows;

    public function setUp(): void
    {
        parent::setUp();
        $this->biz['dao.cache.enabled'] = true;
        $this->biz['dao.cache.annotation'] = true;
        $this->dao = $this->biz->dao('Example:AnnotationExampleDao');
    }

    public function testGetHitCache()
    {
        // no data insert into database.
        $row = $this->seed('Tests\\Example\\Tests\\Seeder\\ExampleSeeder', false)->first();
        $this->redis->set($this->getPrimaryCacheKey($row['id']), $row);

        $geted = $this->dao->get($row['id']);

        $this->assertEquals($row['id'], $geted['id']);
        $this->assertEquals($row['name'], $geted['name']);
    }

    public function testGetMissCache()
    {
        $row = $this->seed('Tests\\Example\\Tests\\Seeder\\ExampleSeeder')->first();

        $geted = $this->dao->get($row['id']);

        $this->assertEquals($row['id'], $geted['id']);
        $this->assertEquals($row['name'], $geted['name']);

        $cache = $this->redis->get($this->getPrimaryCacheKey($row['id']));

        $this->assertEquals($row['id'], $cache['id']);
        $this->assertEquals($row['name'], $cache['name']);
    }

    public function testGetNotFound()
    {
        $this->seed('Tests\\Example\\Tests\\Seeder\\ExampleSeeder');

        $cache = $this->redis->get($this->getPrimaryCacheKey(999));
        $this->assertFalse($cache);

        $geted = $this->dao->get(999);
        $this->assertNull($geted);

        $cache = $this->redis->get($this->getPrimaryCacheKey(999));
        $this->assertFalse($cache);
    }

    public function testGetByNameHitCache()
    {
        // no data insert into database.
        $row = $this->seed('Tests\\Example\\Tests\\Seeder\\ExampleSeeder', false)->first();
        $this->redis->set("dao:{$this->dao->table()}:getByName:{$row['name']}", $this->getPrimaryCacheKey($row['id']));
        $this->redis->set($this->getPrimaryCacheKey($row['id']), $row);

        $geted = $this->dao->getByName($row['name']);

        $this->assertEquals($row['id'], $geted['id']);
        $this->assertEquals($row['name'], $geted['name']);
    }

    public function testGetByNameMissCache()
    {
        $row = $this->seed('Tests\\Example\\Tests\\Seeder\\ExampleSeeder')->first();

        $geted = $this->dao->getByName($row['name']);

        $this->assertEquals($row['id'], $geted['id']);
        $this->assertEquals($row['name'], $geted['name']);

        $this->assertEquals($this->getPrimaryCacheKey($row['id']), $this->redis->get("dao:{$this->dao->table()}:getByName:{$row['name']}"));
        $cache = $this->redis->get($this->getPrimaryCacheKey($row['id']));

        $this->assertEquals($row['id'], $cache['id']);
        $this->assertEquals($row['name'], $cache['name']);
    }

    public function testUpdate()
    {
        $row = $this->seed('Tests\\Example\\Tests\\Seeder\\ExampleSeeder')->first();
        $this->redis->set($this->getPrimaryCacheKey($row['id']), $row);

        $this->dao->update($row['id'], ['content' => 'updated_content']);

        $this->assertFalse($this->redis->get($this->getPrimaryCacheKey($row['id'])));
        $updated = $this->db->query("SELECT * FROM {$this->dao->table()} WHERE id = {$row['id']}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals($row['id'], $updated['id']);
        $this->assertEquals('updated_content', $updated['content']);
    }

    public function testDelete()
    {
        $row = $this->seed('Tests\\Example\\Tests\\Seeder\\ExampleSeeder')->first();
        $this->redis->set($this->getPrimaryCacheKey($row['id']), $row);

        $this->dao->delete($row['id']);

        $this->assertFalse($this->redis->get($this->getPrimaryCacheKey($row['id'])));

        $deleted = $this->db->query("SELECT * FROM {$this->dao->table()} WHERE id = {$row['id']}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertFalse($deleted);
    }

    public function testCreate()
    {
        $row = [
            'name' => 'test_create',
        ];

        $row = $this->dao->create($row);

        $created = $this->db->query("SELECT * FROM {$this->dao->table()} WHERE id = {$row['id']}")->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($row['id'], $created['id']);
        $this->assertEquals($row['name'], $created['name']);
    }

    protected function getPrimaryCacheKey($id)
    {
        return "dao:{$this->dao->table()}:get:{$id}";
    }
}
