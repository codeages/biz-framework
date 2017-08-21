<?php

namespace Codeages\Biz\Framework\Queue\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Queue\Dao\JobDao;

class JobDaoImpl extends GeneralDaoImpl implements JobDao
{
    protected $table = 'biz_queue_job';

    public function getNextJob($queue)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE queue = ? AND (reserved_time = 0 AND available_time <= ?) OR (reserved_time > 0 AND expired_time <= ?) ORDER BY id ASC FOR UPDATE;";
        $now = time();
        return $this->db()->fetchAssoc($sql, array($queue, $now, $now)) ?: null;
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'serializes' => array('body' => 'php'),
            'orderbys' => array('created_time'),
            'conditions' => array(
            ),
        );
    }
}
