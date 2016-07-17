<?php

namespace Codeages\Biz\Framework\Tests\Example\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ExampleDaoImpl extends GeneralDaoImpl implements GeneralDaoInterface
{
    protected $table = 'example';

    public function declares()
    {
        return array(
            'timestamps' => array('created', 'updated'),
            'serializes' => array('ids1' => 'json', 'ids2' => 'delimiter'),
            'conditions' => array(
                'name = :name',
            ),
        );
    }
}
