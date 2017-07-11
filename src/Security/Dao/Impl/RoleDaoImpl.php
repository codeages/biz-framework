<?php

namespace Codeages\Biz\Framework\Security\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Security\Dao\RoleDao;

class RoleDaoImpl extends GeneralDaoImpl implements RoleDao
{
    protected $table = 'role';

    public function declares()
    {
        // TODO: Implement declares() method.
    }
}