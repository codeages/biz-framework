<?php

namespace Codeages\Biz\Framework\Tests\Example\Dao\Impl;

use Codeages\Biz\Framework\Tests\Example\Dao\ExampleDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ExampleDaoImpl extends GeneralDaoImpl implements ExampleDao
{
    protected $table = 'example';

    public function findByName($name, $start, $limit)
    {
        return $this->search(array('name' => $name), array('created' => 'DESC'), $start, $limit);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created', 'updated'),
            'serializes' => array('ids1' => 'json', 'ids2' => 'delimiter'),
            'orderbys' => array('name', 'created'),
            'conditions' => array(
                'name = :name',
            ),
        );
    }
}
