<?php

namespace Codeages\Biz\Framework\Session\Storage;

interface SessionStorage
{
    public function saveSession($session);

    public function deleteSessionBySessId($sessId);

    public function getSessionBySessId($sessId);

    public function gc();
}