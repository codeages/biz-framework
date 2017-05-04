<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\FiredJobDao;

class FiredJobDaoImpl extends GeneralDaoImpl implements FiredJobDao
{
    protected $table = 'fired_job';

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