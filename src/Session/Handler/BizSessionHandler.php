<?php

namespace Codeages\Biz\Framework\Session\Handler;

class BizSessionHandler implements \SessionHandlerInterface
{
    protected $biz;
    protected $lockers = array();
    protected $gcCalled = false;

    public function __construct($biz)
    {
        $this->biz = $biz;
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
        $this->getSessionService()->deleteSessionBySessId($this->dealWithSessionId($session_id));

        return true;
    }

    public function gc($maxlifetime)
    {
        $this->gcCalled = true;
    }

    public function open($save_path, $name)
    {
        return true;
    }

    public function read($session_id)
    {
        $session_id = $this->dealWithSessionId($session_id);
        $this->lockers[] = $this->getLock($session_id);
        if (!in_array($session_id, $this->lockers)) {
            $this->lockers[] = $session_id;
        }

        $session = $this->getSessionService()->getSessionBySessId($session_id);

        return $session['sess_data'];
    }

    public function write($session_id, $session_data)
    {
        $unsavedSession = array(
            'sess_id' => $this->dealWithSessionId($session_id),
            'sess_data' => $session_data,
        );
        $this->getSessionService()->saveSession($unsavedSession);

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

    private function dealWithSessionId($sessionId)
    {
        return md5($sessionId);
    }
}
