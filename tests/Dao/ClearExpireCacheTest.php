<?php

namespace Tests;

use Codeages\Biz\Framework\Dao\CacheStrategy\RowStrategy;
use Codeages\Biz\Framework\Dao\ClearExpireCache;
use Tests\Example\Dao\Impl\ExampleDaoImpl;

class ClearExpireCacheTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
    }
    public function testExecute()
    {
        $dao = new ExampleDaoImpl($this->biz);

        $field = $dao->create(array('name' => 'test'));

        $strategy = new RowStrategy($this->biz['redis'], $this->biz['dao.metadata_reader']);
        $strategy->afterQuery($dao, 'get', array($field['id']), $field);

        $query = $strategy->beforeQuery($dao, 'get',  array($field['id']));
        $this->assertNotEmpty($query);

        $timestamps = time()+60;

        $this->biz['db']->exec("update {$dao->table()} set updated_time = {$timestamps} WHERE id={$field['id']}");

        $command = new ClearExpireCache($this->biz);
        $command->clear(array(
            array(
                'class' => get_class($dao),
                'isMillisecond' => false,
                'updatedTimeColumn' => 'updated_time'
            )));

        $query = $strategy->beforeQuery($dao, 'get', array($field['id']));
        $this->assertEmpty($query);
    }
}