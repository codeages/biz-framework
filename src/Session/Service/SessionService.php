<?php

namespace Codeages\Biz\Framework\Session\Service;

interface SessionService
{
    public function createSession($session);

    public function updateSessionBySessId($sessId, $session);

    public function getSessionBySessId($sessId);

    public function deleteSessionBySessId($sessId);

    public function gc();
}