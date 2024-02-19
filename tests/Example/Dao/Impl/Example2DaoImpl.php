<?php

namespace Tests\Example\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Tests\Example\Dao\ExampleDao;

class Example2DaoImpl extends GeneralDaoImpl implements ExampleDao
{
    protected $table = 'example2';

    public function findByNameAndId($name, $id)
    {
        return $this->findByFields(['name' => $name, 'id' => $id]);
    }

    public function findByName($name, $start, $limit)
    {
        return $this->search(['name' => $name], ['created' => 'DESC'], $start, $limit);
    }

    public function findByIds(array $ids, array $orderBys, $start, $limit)
    {
        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE id IN ({$marks})";

        return $this->db()->fetchAll($this->sql($sql, $orderBys, $start, $limit), $ids) ?: [];
    }

    public function updateByNameAndCode($name, $code, array $fields)
    {
        return $this->update(['name' => $name, 'code' => $code], $fields);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
            'serializes' => ['ids1' => 'json', 'ids2' => 'delimiter', 'null_value' => 'json'],
            'orderbys' => ['name', 'created_time'],
            'conditions' => [
                'name = :name',
                'name pre_LIKE :pre_like',
                'name suF_like :suf_name',
                'name LIKE :like_name',
                'id iN (:ids)',
                'ids1 = :ids1',
            ],
            'cache' => 'table',
        ];
    }
}
