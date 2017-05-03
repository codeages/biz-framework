<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobDao;

class JobDaoImpl extends GeneralDaoImpl implements JobDao
{
    protected $table = 'job_detail';

    public function getWaitingJobByLessThanFireTime($fireTime)
    {
        $sql = "SELECT * FROM 
                (
                  SELECT *, floor(createdTime/60)*60 as formattedCreatedTime FROM {$this->table} 
                  WHERE nextFireTime <= ? AND status = 'waiting'
                ) as {$this->table} 
                ORDER BY formattedCreatedTime ASC , priority DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($fireTime));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime')
        );
    }
}