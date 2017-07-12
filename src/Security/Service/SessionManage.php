<?php

namespace Codeages\Biz\Framework\Security\Service;

interface SessionManage
{
    public function createSession($session);

    public function getSessionBySessionId($sessionId);

    public function deleteSession($sessionId);
}