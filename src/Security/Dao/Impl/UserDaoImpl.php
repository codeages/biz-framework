<?php

namespace Codeages\Biz\Framework\Security\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Security\Dao\UserDao;

class UserDaoImpl extends GeneralDaoImpl implements UserDao
{
    protected $table = 'user';

    public function declares()
    {
        // TODO: Implement declares() method.
    }
}