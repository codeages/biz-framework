<?php

namespace TestProject\Biz\Example\Dao\Impl;

use TestProject\Biz\Example\Dao\ExampleDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class Example3DaoImpl extends GeneralDaoImpl implements ExampleDao
{
    protected $table = 'example3';

    public function findByName($name, $start, $limit)
    {
        return $this->search(array('name' => $name), array('created' => 'DESC'), $start, $limit);
    }

    public function findByNameAndId($name, $id)
    {
        return $this->findByFields(array('name' => $name, 'id' => $id));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array('ids1' => 'json', 'ids2' => 'delimiter'),
            'orderbys' => array('name', 'created_time'),
            'conditions' => array(
                'name = :name'
            )
        );
    }
}
