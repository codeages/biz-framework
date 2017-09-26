<?php

namespace Codeages\Biz\Framework\Session\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Session\Service\SessionService;

class SessionServiceImpl extends BaseService implements SessionService
{
    public function createSession($session)
    {
        return $this->getSessionDao()->create($session);
    }

    public function deleteSessionBySessId($sessId)
    {
        return $this->getSessionDao()->deleteBySessId($sessId);
    }

    public function updateSessionBySessId($sessId, $session)
    {
        $savedSession = $this->getSessionDao()->getBySessId($sessId);
        return $this->getSessionDao()->update($savedSession['id'], $session);
    }

    public function gc()
    {
        return $this->getSessionDao()->deleteByInvalid();
    }

    public function getSessionBySessId($sessId)
    {
        return $this->getSessionDao()->getBySessId($sessId);
    }

    protected function getSessionDao()
    {
        return $this->biz->dao('Session:SessionDao');
    }

    protected function getOnlineDao()
    {
        return $this->biz->dao('Session:OnlineDao');
    }
}