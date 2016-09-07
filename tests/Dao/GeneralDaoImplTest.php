<?php

namespace Codeages\Biz\Framework\Tests\Dao;

use Codeages\Biz\Framework\Tests\Example\Dao\Impl\ExampleDaoImpl;
use Codeages\Biz\Framework\Tests\Example\ExampleKernel;

class GeneralDaoImplTest extends \PHPUnit_Framework_TestCase
{
    const NOT_EXIST_ID = 9999;

    public function __construct()
    {
        $this->kernel = new ExampleKernel();
        $this->kernel->boot();

        $this->kernel->recreateDatabase();

        $this->kernel['example_dao'] = $this->kernel->dao(function ($kernel){
            return new ExampleDaoImpl($kernel);
        });

        $this->dao = $this->kernel['example_dao'];
    }

    public function setUp()
    {
        $this->kernel->emptyDatabase();
    }

    public function testGet()
    {
        $row = $this->dao->create(array(
            'name' => 'test1',
        ));

        $found = $this->dao->get($row['id']);
        $this->assertEquals($row['id'], $found['id']);

        $found = $this->dao->get(self::NOT_EXIST_ID);
        $this->assertEquals(null, $found);
    }

    public function testCreate()
    {
        $fields = array(
            'name' => 'test1',
            'ids1' => array(1, 2, 3),
            'ids2' => array(1, 2, 3),
        );

        $before = time();

        $saved = $this->dao->create($fields);

        $this->assertEquals($fields['name'], $saved['name']);
        $this->assertTrue(is_array($saved['ids1']));
        $this->assertCount(3, $saved['ids1']);
        $this->assertTrue(is_array($saved['ids2']));
        $this->assertCount(3, $saved['ids2']);
        $this->assertGreaterThanOrEqual($before, $saved['created']);
        $this->assertGreaterThanOrEqual($before, $saved['updated']);
    }

    public function testUpdate()
    {
        $row = $this->dao->create(array(
            'name' => 'test1',
        ));

        $fields = array(
            'name' => 'test2',
            'ids1' => array(1, 2),
            'ids2' => array(1, 2),
        );

        $before = time();
        $saved = $this->dao->update($row['id'], $fields);

        $this->assertEquals($fields['name'], $saved['name']);
        $this->assertTrue(is_array($saved['ids1']));
        $this->assertCount(2, $saved['ids1']);
        $this->assertTrue(is_array($saved['ids2']));
        $this->assertCount(2, $saved['ids2']);
        $this->assertGreaterThanOrEqual($before, $saved['updated']);
    }

    public function testDelete()
    {
        $row = $this->dao->create(array(
            'name' => 'test1',
        ));

        $deleted = $this->dao->delete($row['id']);

        $this->assertEquals(1, $deleted);
    }

    public function testWave()
    {
        $row = $this->dao->create(array(
            'name' => 'test1',
        ));

        $diff = array('counter1' => 1, 'counter2' => 2);
        $waved = $this->dao->wave(array($row['id']), $diff);
        $row = $this->dao->get($row['id']);

        $this->assertEquals(1, $waved);
        $this->assertEquals(1, $row['counter1']);
        $this->assertEquals(2, $row['counter2']);

        $diff = array('counter1' => -1, 'counter2' => -1);
        $waved = $this->dao->wave(array($row['id']), $diff);
        $row = $this->dao->get($row['id']);

        $this->assertEquals(1, $waved);
        $this->assertEquals(0, $row['counter1']);
        $this->assertEquals(1, $row['counter2']);
    }

    public function testSearch()
    {
        $this->dao->create(array('name' => 'test1'));
        $this->dao->create(array('name' => 'test2'));
        $this->dao->create(array('name' => 'test3'));

        $found = $this->dao->search(array('name' => 'test2'), array('created' => 'desc'), 0, 100);

        $this->assertEquals(1, count($found));
        $this->assertEquals('test2', $found[0]['name']);
    }

    public function testCount()
    {
        $this->dao->create(array('name' => 'test1'));
        $this->dao->create(array('name' => 'test2'));
        $this->dao->create(array('name' => 'test3'));

        $count = $this->dao->count(array('name' => 'test2'));

        $this->assertEquals(1, $count);
    }

    public function testTransactional()
    {
        $result = $this->dao->db()->transactional(function ($connection){
            return 1;
        });

        $this->assertEquals(1, $result);
    }
}
