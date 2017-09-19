<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobDao;

class JobDaoImpl extends GeneralDaoImpl implements JobDao
{
    protected $table = 'job';

    public function findWaitingJobsByLessThanFireTime($fireTime)
    {
        $sql = "SELECT * FROM 
                (
                  SELECT * FROM {$this->table} 
                  WHERE enabled = 1 AND next_fire_time <= ? AND deleted = 0
                ) as {$this->table} 
                ORDER BY next_fire_time ASC , priority DESC";

        return $this->db()->fetchAll($sql, array($fireTime));
    }

    public function getByName($name)
    {
        return $this->getByFields(array(
            'name' => $name,
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(
                'args' => 'json',
            ),
            'conditions' => array(
                'name like :name',
                'class like :class',
                'source like :source',
                'deleted = :deleted',
            ),
        );
    }
}
