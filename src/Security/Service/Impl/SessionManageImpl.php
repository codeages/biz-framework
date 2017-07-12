<?php

namespace Codeages\Biz\Framework\Security\Service\Impl;

use Codeages\Biz\Framework\Security\Service\SessionManage;
use Codeages\Biz\Framework\Service\BaseService;

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

    public function deleteSession($sessionId)
    {
        $this->getSessionDao()->deleteBySessionId($sessionId);
    }

    protected function getSessionDao()
    {
        return $this->biz->service('Security:SessionDao');
    }
}