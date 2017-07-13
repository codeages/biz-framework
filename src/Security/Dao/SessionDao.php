<?php

namespace Codeages\Biz\Framework\Security\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SessionDao extends GeneralDaoInterface
{
    public function getBySessionId($sessionId);

    public function deleteBySessionId($sessionId);

    public function searchBySessionTime($sessionTime, $limit);

    public function deleteByLessThanSessTime($sessTimeLT);

    public function updateBySessionId($sessionId, $session);
}