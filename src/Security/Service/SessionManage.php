<?php

namespace Codeages\Biz\Framework\Security\Service;

interface SessionManage
{
    public function createSession($session);

    public function getSessionBySessionId($sessionId);

    public function deleteSessionBySessionId($sessionId);

    public function deleteSessionsByUserId($userId);

    public function deleteInvalidSession($sessionTime);

    public function refresh($sessionId, $data);

    public function search($condition, $order, $start, $limit);

    public function count($condition);

    public function countOnline($retentionTime);

    public function countLogin($retentionTime);
}