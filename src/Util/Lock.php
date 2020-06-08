<?php

namespace Codeages\Biz\Framework\Util;

/**
 * @deprecated 2.0
 */
class Lock
{
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function get($lockName, $lockTime = 30)
    {
        return $this->getConnection()->getLock("SELECT GET_LOCK(?,?) AS getLock", array('locker_' . $lockName, $lockTime));
    }

    public function release($lockName)
    {
        return $this->getConnection()->releaseLock("SELECT RELEASE_LOCK(?) AS releaseLock", array('locker_' . $lockName));
    }

    protected function getConnection()
    {
        return $this->biz['db'];
    }
}
