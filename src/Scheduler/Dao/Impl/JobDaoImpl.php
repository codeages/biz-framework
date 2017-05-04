<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobDao;

class JobDaoImpl extends GeneralDaoImpl implements JobDao
{
    protected $table = 'job_detail';

    public function findWaitingJobsByLessThanFireTime($fireTime)
    {
        $sql = "SELECT * FROM 
                (
                  SELECT *, floor(createdTime/60)*60 as formattedCreatedTime FROM {$this->table} 
                  WHERE nextFireTime <= ? AND status = 'waiting' AND enabled = 1
                ) as {$this->table} 
                ORDER BY formattedCreatedTime ASC , priority DESC";

        return $this->db()->fetchAll($sql, array($fireTime));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array(
                'data' => 'json',
            )
        );
    }
}