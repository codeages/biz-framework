<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobFiredDao;

class JobFiredDaoImpl extends GeneralDaoImpl implements JobFiredDao
{
    protected $table = 'job_fired';

    public function getByStatus($status)
    {
        $sql = "SELECT * FROM 
                (
                  SELECT *, floor(firedTime/60)*60 as formattedFiredTime FROM {$this->table} 
                  WHERE firedTime <= ? AND status = ?
                ) as {$this->table} 
                ORDER BY formattedFiredTime ASC , priority DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, array(strtotime('+1 minutes'), $status));
    }

    public function findByJobId($jobId)
    {
        return $this->findByFields(array(
            'jobId' => $jobId
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime')
        );
    }
}