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

    public function setUp()
    {
        parent::setUp();
        $this->biz['dao.cache.enabled'] = true;
        $this->biz['dao.cache.annotation'] = true;
        $this->dao = $this->biz->dao('Example:AnnotationExampleDao');
    }

    public function testGet_HitCache()
    {
        $row = $this->seed('Tests\\Example\\Tests\\Seeder\\ExampleSeeder', false)->first();
        $this->redis->set($this->getPrimaryCacheKey($row['id']), $row);

        $geted = $this->dao->get($row['id']);

        $this->assertEquals($row['id'], $geted['id']);
        $this->assertEquals($row['name'], $geted['name']);
    }

    public function testGet_MissCache()
    {
        $row = $this->seed('Tests\\Example\\Tests\\Seeder\\ExampleSeeder')->first();

        $geted = $this->dao->get($row['id']);

        $this->assertEquals($row['id'], $geted['id']);
        $this->assertEquals($row['name'], $geted['name']);

        $cache = $this->redis->get($this->getPrimaryCacheKey($row['id']));

        $this->assertEquals($row['id'], $cache['id']);
        $this->assertEquals($row['name'], $cache['name']);
    }

    public function testGet_NotFound()
    {
        $cache = $this->redis->get($this->getPrimaryCacheKey(999));
        $this->assertFalse($cache);

        $geted = $this->dao->get(999);
        $this->assertNull($geted);

        $cache = $this->redis->get($this->getPrimaryCacheKey(999));
        $this->assertNull($cache);
    }

    protected function getPrimaryCacheKey($id)
    {
        return "dao:{$this->dao->table()}:get:{$id}";
    }
}