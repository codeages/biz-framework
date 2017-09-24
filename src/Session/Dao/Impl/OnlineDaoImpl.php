<?php

namespace Codeages\Biz\Framework\Session\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Session\Dao\OnlineDao;

class OnlineDaoImpl extends GeneralDaoImpl implements OnlineDao
{
    protected $table = 'biz_online';

    public function getBySessId($sessionId)
    {
        return $this->getByFields(array('sess_id' => $sessionId));
    }

    public function deleteByInvalid()
    {
        $sql = "DELETE FROM {$this->table} WHERE sess_time < (? - lifetime) ";
        return $this->db()->executeUpdate($sql, array(time()));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'access_time'),
            'orderbys' => array('created_time', 'id', 'access_time'),
            'serializes' => array(
            ),
            'conditions' => array(
                'access_time > :gt_access_time',
                'user_id > :gt_user_id',
                'user_id = :user_id'
            ),
        );
    }
}