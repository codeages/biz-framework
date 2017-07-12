<?php

namespace Codeages\Biz\Framework\Security\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Security\Dao\SessionDao;

class SessionDaoImpl extends GeneralDaoImpl implements SessionDao
{
    protected $table = 'session';

    public function deleteBySessionId($sessionId)
    {
        // TODO: Implement deleteBySessionId() method.
    }

    public function getBySessionId($sessionId)
    {
        // TODO: Implement getBySessionId() method.
    }

    public function declares()
    {
        // TODO: Implement declares() method.
    }
}