<?php

namespace Codeages\Biz\Framework\Session\Storage\Impl;

use Codeages\Biz\Framework\Session\Storage\SessionStorage;

class RedisSessionStorageImpl implements SessionStorage
{
    private $biz;

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function deleteSessionBySessId($sessId)
    {
        return $this->getRedis()->delete($this->getSessionPrefix().':'.$sessId);
    }

    public function getSessionBySessId($sessId)
    {
        return $this->getRedis()->get($this->getSessionPrefix().':'.$sessId);
    }

    public function saveSession($session)
    {
        $session['sess_time'] = time();
        $this->getRedis()->setex($this->getSessionPrefix().':'.$session['sess_id'], $this->getMaxLifeTime(), $session['sess_data']);;
        return $session;
    }

    public function gc()
    {
        return true;
    }

    protected function getSessionPrefix()
    {
        return 'biz_session_';
    }

    protected function getMaxLifeTime()
    {
        return $this->biz['session.options']['max_life_time'];
    }

    protected function getRedis()
    {
        return $this->biz['redis'];
    }
}