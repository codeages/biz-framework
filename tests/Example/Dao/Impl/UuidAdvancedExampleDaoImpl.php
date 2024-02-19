<?php

namespace Tests\Example\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Tests\Example\Dao\UuidExampleDao;

class UuidAdvancedExampleDaoImpl extends AdvancedDaoImpl implements UuidExampleDao
{
    protected $table = 'example_uuid';

    public function declares()
    {
        return [
            'id_generator' => 'uuid',
            'orderbys' => ['id'],
            'timestamps' => ['created_time', 'updated_time'],
            'conditions' => [
                'name = :name',
            ],
        ];
    }
}
