<?php

namespace Codeages\Biz\Framework\Security\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Security\Dao\SessionDao;

class SessionDaoImpl extends GeneralDaoImpl implements SessionDao
{
    protected $table = 'sessions';

    public function deleteBySessionId($sessionId)
    {
        $session = $this->getBySessionId($sessionId);
        return $this->delete($session['id']);
    }

    public function getBySessionId($sessionId)
    {
        return $this->getByFields(array('sess_id' => $sessionId));
    }

    public function searchBySessionTime($sessionTime, $limit)
    {
        $limit = (int) $limit;
        $sql = "SELECT * FROM {$this->table} WHERE `sess_time` < ? LIMIT {$limit};";

        return $this->db()->fetchAll($sql, array($sessionTime));
    }

    public function deleteByIds($ids)
    {
        if (empty($ids)) {
            return 0;
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "DELETE FROM {$this->table} WHERE `id` in ( {$marks} );";

        return $this->db()->executeUpdate($sql, $ids);
    }

    public function updateBySessionId($sessionId, $session)
    {
        $session['sess_time'] = time();
        return $this->updateByConditions(array('sess_id' => $sessionId), $session);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array('id'),
            'conditions' => array(
                'sess_time > :sess_time_GT',
                'sess_user_id <> :sess_user_id_NE',
                'sess_time < :sess_time_LT'
            )
        );
    }
}