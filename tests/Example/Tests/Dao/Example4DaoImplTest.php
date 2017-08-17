<?php

namespace Tests\Example\Tests\Dao;

use Doctrine\Common\Collections\ArrayCollection;
use Tests\Example\Dao\ExampleDao;
use Tests\IntegrationTestCase;
use PDO;

class Example4DaoImplTest extends IntegrationTestCase
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
        $this->biz['dao.cache.enabled'] = false;
        $this->biz['dao.cache.annotation'] = false;
        $this->dao = $this->biz->dao('Example:Example4Dao');
    }

    public function testGet()
    {
        $row = $this->dao->create(array('name' => 'test', 'content' => 'created_content'));
        $rowGet = $this->dao->get($row['id']);

        $updated = $this->db->query("SELECT * FROM {$this->dao->table()} WHERE id = {$row['id']}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals($row['id'], $updated['id']);
        $this->assertEquals($row['content'],$rowGet['content']);
        $this->assertEquals(base64_encode('created_content'), $updated['content']);
    }

    public function testUpdate()
    {
        $row = $this->dao->create(array('name' => 'test', 'content' => 'created_content'));

        $this->dao->update($row['id'], array('content' => 'updated_content'));

        $updated = $this->db->query("SELECT * FROM {$this->dao->table()} WHERE id = {$row['id']}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals($row['id'], $updated['id']);
        $this->assertEquals(base64_encode('updated_content'), $updated['content']);
    }

    public function testCreate()
    {
        $row = array(
            'name' => 'test_create',
            'content' => 'test_content'
        );

        $row = $this->dao->create($row);

        $created = $this->db->query("SELECT * FROM {$this->dao->table()} WHERE id = {$row['id']}")->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($row['id'], $created['id']);
        $this->assertEquals($row['name'], $created['name']);
        $this->assertEquals(base64_encode('test_content'), $created['content']);
    }
}