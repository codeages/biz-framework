<?php

namespace Codeages\Biz\Framework\Xapi\Dao\Impl;


use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\Framework\Xapi\Dao\StatementDao;

class StatementDaoImpl extends AdvancedDaoImpl implements StatementDao
{
    protected $table = 'biz_xapi_statement';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'orderbys' => array(
                'created_time'
            ),
            'serializes' => array(
                'data' => 'json'
            ),
            'conditions' => array(
                'status = :status'
            ),
        );
    }
}
