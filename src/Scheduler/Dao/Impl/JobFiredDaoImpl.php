<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobFiredDao;

class JobFiredDaoImpl extends GeneralDaoImpl implements JobFiredDao
{
    protected $table = 'job_fired';

    public function getByStatus($status)
    {
        return $this->getByFields(array('status' => $status));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime')
        );
    }
}