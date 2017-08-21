<?php

namespace Codeages\Biz\Framework\Queue\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Queue\Dao\JobDao;

class JobDaoImpl extends GeneralDaoImpl implements JobDao
{
    protected $table = 'biz_queue_job';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'serializes' => array('context' => 'json'),
            'orderbys' => array('created_time'),
            'conditions' => array(
                'level = :level',
                'target_type = :target_type',
                'target_id = :target_id',
                'action = :action',
                'user_id = :user_id',
                'ip = :ip',
            ),
        );
    }
}
