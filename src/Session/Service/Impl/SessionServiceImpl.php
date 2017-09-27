<?php

namespace Codeages\Biz\Framework\Session\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Session\Service\SessionService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class SessionServiceImpl extends BaseService implements SessionService
{
    public function saveSession($session)
    {
        if(!ArrayToolkit::requireds($session, array('sess_id', 'sess_data'))) {
            throw new InvalidArgumentException('args is invalid.');
        }

        $session['sess_deadline'] = time() + $this->getMaxLifeTime();

        if ($this->isRedisStorage()) {
            $session['sess_time'] = time();
            $this->getRedis()->setex($this->getSessionPrefix().':'.$session['sess_id'], $this->getMaxLifeTime(), $session['sess_data']);;
            return $session;
        } else {
            $savedSession = $this->getSessionDao()->getBySessId($session['sess_id']);
            if (empty($savedSession)) {
                return $this->getSessionDao()->create($session);
            } else {
                return $this->getSessionDao()->update($savedSession['id'], $session);
            }
        }
    }

    public function deleteSessionBySessId($sessId)
    {
        if ($this->isRedisStorage()) {
            return $this->getRedis()->delete($this->getSessionPrefix().':'.$sessId);
        } else {
            return $this->getSessionDao()->deleteBySessId($sessId);
        }
    }

    public function getSessionBySessId($sessId)
    {
        if ($this->isRedisStorage()) {
            return $this->getRedis()->get($this->getSessionPrefix().':'.$sessId);
        } else {
            return $this->getSessionDao()->getBySessId($sessId);
        }
    }

    public function gc()
    {
        if (!$this->isRedisStorage()) {
            return $this->getSessionDao()->deleteBySessDeadlineLessThan(time());
        }
    }

    protected function getSessionDao()
    {
        return $this->biz->dao('Session:SessionDao');
    }

    protected function getSessionPrefix()
    {
        return $this->biz['session.options']['sess_prefix'];
    }

    protected function getMaxLifeTime()
    {
        return $this->biz['session.options']['max_life_time'];
    }

    protected function isRedisStorage()
    {
        return $this->biz['session.options']['redis_storage'];
    }

    protected function getRedis()
    {
        return $this->biz['redis'];
    }
}