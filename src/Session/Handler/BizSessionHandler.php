<?php

namespace Codeages\Biz\Framework\Session\Handler;

class BizSessionHandler implements \SessionHandlerInterface
{
    protected $biz;
    protected $lockers = array();
    protected $gcCalled = false;

    protected $maxLifeTime = 86400;

    public function __construct($biz, $options = array())
    {
        $this->biz = $biz;
        if (!empty($options['max_life_time'])) {
            $this->maxLifeTime = $options['max_life_time'];
        }
    }

    public function close()
    {
        while ($locker = array_shift($this->lockers)) {
            $this->releaseLock($locker);
        }

        if ($this->gcCalled) {
            $this->getSessionService()->gc();
        }

        return true;
    }

    public function destroy($session_id)
    {
        $this->getSessionService()->deleteSessionBySessId($session_id);

        return true;
    }

    public function gc($maxlifetime)
    {
        $this->gcCalled = true;
    }

    public function open($save_path, $name)
    {
        $this->gc($this->maxLifeTime);
        return true;
    }

    public function read($session_id)
    {
        $this->lockers[] = $this->getLock($session_id);
        if (!in_array($session_id, $this->lockers)) {
            $this->lockers[] = $session_id;
        }

        $session = $this->getSessionService()->getSessionBySessId($session_id);

        return $session['sess_data'];
    }

    public function write($session_id, $session_data)
    {
        $user = $this->biz['user'];
        if (empty($user['id'])) {
            $userId = 0;
        } else {
            $userId = $user['id'];
        }

        $session = $this->getSessionService()->getSessionBySessId($session_id);
        $unsavedSession = array(
            'sess_id' => $session_id,
            'sess_data' => $session_data,
            'sess_time' => time(),
            'sess_lifetime' => $this->maxLifeTime,
            'sess_user_id' => $userId,
            'source' => 'web',
        );
        if (empty($session)) {
            $this->getSessionService()->createSession($unsavedSession);
        } else {
            $this->getSessionService()->updateSessionBySessId($session_id, $unsavedSession);
        }

        return true;
    }

    public function getLock($lockName, $lockTime = 30)
    {
        $result = $this->biz['db']->fetchAssoc("SELECT GET_LOCK('sess_{$lockName}', {$lockTime}) AS getLock");

        return $result['getLock'];
    }

    public function releaseLock($lockName)
    {
        $result = $this->biz['db']->fetchAssoc("SELECT RELEASE_LOCK('sess_{$lockName}') AS releaseLock");

        return $result['releaseLock'];
    }

    private function getSessionService()
    {
        return $this->biz->service('Session:SessionService');
    }
}
