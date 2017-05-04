<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobLogDao;

class JobLogDaoImpl extends GeneralDaoImpl implements JobLogDao
{
    protected $table = 'job_log';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'serializes' => array(
                'data' => 'json',
            )
        );
    }
}