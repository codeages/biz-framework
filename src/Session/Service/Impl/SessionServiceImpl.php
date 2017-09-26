<?php

namespace Codeages\Biz\Framework\Session\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Session\Service\SessionService;

class SessionServiceImpl extends BaseService implements SessionService
{
    public function saveSession($session)
    {
        $savedSession = $this->getSessionDao()->getBySessId($session['sess_id']);
        if (empty($savedSession)) {
            return $this->getSessionDao()->create($session);
        } else {
            return $this->getSessionDao()->update($savedSession['id'], $session);
        }
    }

    public function deleteSessionBySessId($sessId)
    {
        return $this->getSessionDao()->deleteBySessId($sessId);
    }

    public function getSessionBySessId($sessId)
    {
        return $this->getSessionDao()->getBySessId($sessId);
    }

    public function gc()
    {
        return $this->getSessionDao()->deleteByInvalid();
    }

    protected function getSessionDao()
    {
        return $this->biz->dao('Session:SessionDao');
    }
}