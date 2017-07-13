<?php

namespace Codeages\Biz\Framework\Security\Service\Impl;

use Codeages\Biz\Framework\Security\Service\SessionManage;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class SessionManageImpl extends BaseService implements SessionManage
{
    public function createSession($session)
    {
        return $this->getSessionDao()->create($session);
    }

    public function getSessionBySessionId($sessionId)
    {
        return $this->getSessionDao()->getBySessionId($sessionId);
    }

    public function deleteSessionBySessionId($sessionId)
    {
        $this->getSessionDao()->deleteBySessionId($sessionId);
    }

    public function deleteSessionsByUserId($userId)
    {
        return $this->getSessionDao()->deleteByUserId($userId);
    }

    public function deleteInvalidSession($sessionTime)
    {
        $sessions = $this->getSessionDao()->search(array(
            'sess_time_LT' => $sessionTime
        ), array(), 0, 1000);
        $ids = ArrayToolkit::column($sessions, 'id');

        return $this->getSessionDao()->deleteByIds($ids);
    }

    public function refresh($sessionId, $data)
    {
        return $this->getSessionDao()->updateBySessionId($sessionId, array('sess_data' => $data));
    }

    public function search($condition, $order, $start, $limit)
    {
        return $this->getSessionDao()->search($condition, $order, $start, $limit);
    }

    public function count($condition)
    {
        return $this->getSessionDao()->count($condition);
    }

    public function countOnline($retentionTime)
    {
        return $this->getSessionDao()->count(array(
            'sess_time_GT' => $retentionTime,
        ));
    }

    public function countLogin($retentionTime)
    {
        return $this->getSessionDao()->count(array(
            'sess_time_GT' => $retentionTime,
            'sess_user_id_NE' => 0
        ));
    }

    protected function getSessionDao()
    {
        return $this->biz->dao('Security:SessionDao');
    }
}