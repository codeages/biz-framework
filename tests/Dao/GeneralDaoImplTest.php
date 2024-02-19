<?php

namespace Tests;

use Codeages\Biz\Framework\Dao\DaoException;
use Tests\Example\Dao\ExampleDao;

class GeneralDaoImplTest extends IntegrationTestCase
{
    const NOT_EXIST_ID = 9999;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @group current
     */
    public function testGet()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->get($dao);
        }
    }

    public function testUpdateByNameAndCode()
    {
        foreach ($this->getTestDao() as $dao) {
            $dao = $this->getDao($dao);
            $row = $dao->create([
                'name' => 'test1',
                'code' => 'test1',
            ]);

            $affected = $dao->updateByNameAndCode('test1', 'test1', ['content' => 'test']);

            $this->assertEquals(1, $affected);
        }
    }

    private function getTestDao()
    {
        return [
            'Example:ExampleDao',
            'Example:Example2Dao',
            'Example:Example3Dao',
        ];
    }

    private function get($dao)
    {
        $dao = $this->getDao($dao);
        $row = $dao->create([
            'name' => 'test1',
        ]);

        $found = $dao->get($row['id']);
        $this->assertEquals($row['id'], $found['id']);

        $found = $dao->get(self::NOT_EXIST_ID);
        $this->assertEquals(null, $found);
    }

    public function testCreate()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->create($dao);
        }
    }

    private function create($dao)
    {
        $dao = $this->getDao($dao);

        $fields = [
            'name' => 'test1',
            'ids1' => [1, 2, 3],
            'ids2' => [1, 2, 3],
        ];

        $before = time();

        $saved = $dao->create($fields);

        $this->assertEquals($fields['name'], $saved['name']);
        $this->assertTrue(is_array($saved['ids1']));
        $this->assertCount(3, $saved['ids1']);
        $this->assertTrue(is_array($saved['ids2']));
        $this->assertCount(3, $saved['ids2']);
        $this->assertGreaterThanOrEqual($before, $saved['created_time']);
        $this->assertGreaterThanOrEqual($before, $saved['updated_time']);
    }

    public function testUpdate()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->update($dao);
        }
    }

    private function update($dao)
    {
        $dao = $this->getDao($dao);

        $row = $dao->create([
            'name' => 'test1',
        ]);

        $fields = [
            'name' => 'test2',
            'ids1' => [1, 2],
            'ids2' => [1, 2],
        ];

        $before = time();
        $saved = $dao->update($row['id'], $fields);

        $this->assertEquals($fields['name'], $saved['name']);
        $this->assertTrue(is_array($saved['ids1']));
        $this->assertCount(2, $saved['ids1']);
        $this->assertTrue(is_array($saved['ids2']));
        $this->assertCount(2, $saved['ids2']);
        $this->assertGreaterThanOrEqual($before, $saved['updated_time']);
    }

    public function testDelete()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->delete($dao);
        }
    }

    private function delete($dao)
    {
        $dao = $this->getDao($dao);

        $row = $dao->create([
            'name' => 'test1',
        ]);

        $deleted = $dao->delete($row['id']);

        $this->assertEquals(1, $deleted);
    }

    public function testWave()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->wave($dao);
        }
    }

    public function wave($dao)
    {
        $dao = $this->getDao($dao);

        $row = $dao->create([
            'name' => 'test1',
        ]);

        $diff = ['counter1' => 1, 'counter2' => 2];
        $waved = $dao->wave([$row['id']], $diff);
        $row = $dao->get($row['id']);

        $this->assertEquals(1, $waved);
        $this->assertEquals(1, $row['counter1']);
        $this->assertEquals(2, $row['counter2']);

        $diff = ['counter1' => -1, 'counter2' => -1];
        $waved = $dao->wave([$row['id']], $diff);
        $row = $dao->get($row['id']);

        $this->assertEquals(1, $waved);
        $this->assertEquals(0, $row['counter1']);
        $this->assertEquals(1, $row['counter2']);
    }

    public function testLikeSearch()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->search($this->getDao($dao));
        }
    }

    private function search($dao)
    {
        $dao->create(['name' => 'pre_test1']);
        $dao->create(['name' => 'pre_test2']);
        $dao->create(['name' => 'test3_suf']);
        $dao->create(['name' => 'test4_suf']);
        $dao->create(['name' => 'test5']);

        $preNames = $dao->search(['pre_like' => 'pre_'], ['name' => 'desc'], 0, 100);
        $sufNames = $dao->search(['suf_name' => '_suf'], ['name' => 'desc'], 0, 100);
        $likeNames = $dao->search(['like_name' => 'test'], ['name' => 'desc'], 0, 100);

        $this->assertCount(2, $preNames);
        $this->assertCount(2, $sufNames);
        $this->assertCount(5, $likeNames);
        $this->assertEquals('pre_test2', $preNames[0]['name']);
        $this->assertEquals('test3_suf', $sufNames[1]['name']);
        $this->assertEquals('pre_test1', $likeNames[4]['name']);
    }

    public function testInSearch()
    {
        $dao = $this->getExampleDao();

        $tmp1 = $dao->create(['name' => 'pre_test1']);
        $dao->create(['name' => 'pre_test2']);
        $tmp2 = $dao->create(['name' => 'test3_suf']);
        $dao->create(['name' => 'test4_suf']);

        $results = $dao->search(['ids' => [$tmp1['id'], $tmp2['id']]], ['created_time' => 'desc'], 0, 100);

        $this->assertCount(2, $results);

        $results = $dao->search(['ids' => []], ['created_time' => 'desc'], 0, 100);

        $this->assertCount(4, $results);
    }

    public function testCount()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->daoCount($dao);
        }
    }

    private function daoCount($dao)
    {
        $alias = $dao;
        $dao = $this->getDao($alias);

        $dao->create(['name' => 'test1']);
        $dao->create(['name' => 'test2']);
        $dao->create(['name' => 'test3']);

        $count = $dao->count(['name' => 'test2']);

        $this->assertEquals(1, $count);
    }

    public function testFindInFields()
    {
        $dao = $this->getExampleDao();

        $dao->create(['name' => 'test1', 'ids1' => ['1111'], 'ids2' => ['1111']]);
        $dao->create(['name' => 'test1', 'ids1' => ['1111'], 'ids2' => ['2222']]);
        $dao->create(['name' => 'test2', 'ids1' => ['1111'], 'ids2' => ['3333']]);
        $result = $dao->findByNameAndId('test1', '["1111"]');

        $this->assertEquals(count($result), 2);
    }

    public function testTransactional()
    {
        foreach ($this->getTestDao() as $dao) {
            $this->transactional($dao);
        }
    }

    public function transactional($dao)
    {
        $dao = $this->getDao($dao);

        $result = $dao->db()->transactional(function () {
            return 1;
        });

        $this->assertEquals(1, $result);
    }

    public function testNullValueUnserializer()
    {
        $dao = $this->getExampleDao();

        $row = $dao->create(['name' => 'test1']);

        $result = $dao->get($row['id']);
        $this->assertIsArray($result['null_value']);
    }

    public function testOrderBysInject()
    {
        $dao = $this->getExampleDao();

        $this->expectException(DaoException::class);
        $row = $dao->create(['name' => 'test1']);

        $this->expectException(DaoException::class);
        $dao->findByIds([1], ['; SELECT * FROM example'], 0, 10);

        $this->expectException(DaoException::class);
        $dao->findByIds([1], ['id' => '; SELECT * FROM example'], 0, 10);
    }

    public function testStartInject()
    {
        $this->expectException(DaoException::class);
        $dao = $this->getExampleDao();

        $row = $dao->create(['name' => 'test1']);

        $dao->findByIds([1], ['created_time' => 'desc'], '; SELECT * FROM example', 10);
        $dao->findByIds([1], ['created_time' => 'desc'], 0, "; UPDATE example SET name = 'inject' WHERE id = 1");
    }

    public function testLimitInject()
    {
        $this->expectException(DaoException::class);
        $dao = $this->getExampleDao();

        $row = $dao->create(['name' => 'test1']);
        $dao->findByIds([1], ['created_time' => 'desc'], 0, "; UPDATE example SET name = 'inject' WHERE id = 1");
    }

    public function testNonInject()
    {
        $dao = $this->getExampleDao();

        $row1 = $dao->create(['name' => 'testNonInject_1']);

        $result = $dao->findByIds([$row1['id']], ['created_time' => 'desc'], '0', '2');
        $this->assertCount(1, $result);

        $row2 = $dao->create(['name' => 'testNonInject_2']);
        $result = $dao->findByIds([$row1['id'], $row2['id']], ['created_time' => 'desc'], '0', 1);
        $this->assertCount(1, $result);

        $result = $dao->findByIds([$row1['id'], $row2['id']], ['created_time' => 'desc'], '0', 10);
        $this->assertCount(2, $result);
    }

    public function testOnlySetStart()
    {
        $this->expectException(DaoException::class);
        $dao = $this->getExampleDao();

        $row = $dao->create(['name' => 'test1']);
        $result = $dao->findByIds([1, 2], ['created_time' => 'desc'], '0', null);
    }

    public function testOnlySetLimit()
    {
        $this->expectException(DaoException::class);
        $dao = $this->getExampleDao();

        $row = $dao->create(['name' => 'test1']);
        $result = $dao->findByIds([1, 2], ['created_time' => 'desc'], null, 10);
    }

    public function testSerializes()
    {
        $dao = $this->getExampleDao();

        $row = $dao->create([
            'name' => 'test1',
            'php_serialize_value' => ['value' => 'i_am_php_serialized_value'],
            'json_serialize_value' => ['value' => 'i_am_json_serialized_value'],
            'delimiter_serialize_value' => ['i_am_delimiter_serialized_value'],
        ]);

        foreach (['php', 'json'] as $key) {
            $this->assertEquals($row[$key.'_serialize_value']['value'], "i_am_{$key}_serialized_value");
        }

        $this->assertEquals($row['delimiter_serialize_value'], ['i_am_delimiter_serialized_value']);
    }

    public function testSearchWithColumns()
    {
        $this->getExampleDao()->create(['name' => 'test1', 'ids1' => ['1111'], 'ids2' => ['1111']]);
        $this->getExampleDao()->create(['name' => 'test1', 'ids1' => ['2222'], 'ids2' => ['2222']]);
        $this->getExampleDao()->create(['name' => 'test2', 'ids1' => ['3333'], 'ids2' => ['3333']]);

        $results = $this->getExampleDao()->search(['name' => 'test2'], [], 0, 10, ['name', 'ids1']);
        $this->assertCount(1, $results);
        $this->assertEquals(
            ['name' => 'test2', 'ids1' => ['3333']],
            $results[0]
        );
    }

    public function testSearchWithErrorColumns()
    {
        $this->expectException(DaoException::class);
        $this->getExampleDao()->search(['name' => 'test2'], [], 0, 10, ['* from example;#']);
    }

    public function testPickIdAndUpdatedTimesByUpdatedTimeGT()
    {
        $dao = $this->getExampleDao();

        $dao->create(['name' => 'test1', 'ids1' => ['1111'], 'ids2' => ['1111']]);
        $results = $dao->pickIdAndUpdatedTimesByUpdatedTimeGT(time() - 60 * 60, 0, 1000, 'updated_time');
        $this->assertCount(1, $results);
        $dao->create(['name' => 'test2', 'ids1' => ['2222'], 'ids2' => ['2222']]);
        $results = $dao->pickIdAndUpdatedTimesByUpdatedTimeGT(time() - 60 * 60, 0, 1000, 'updated_time');
        $this->assertCount(2, $results);
    }

    /**
     * @param $alias
     *
     * @return mixed
     */
    public function getDao($alias)
    {
        return $this->biz->dao($alias);
    }

    /**
     * @return ExampleDao
     */
    public function getExampleDao()
    {
        return $this->getDao('Example:ExampleDao');
    }
}
