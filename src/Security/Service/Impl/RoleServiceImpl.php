<?php

namespace Codeages\Biz\Framework\Security\Service\Impl;

use Codeages\Biz\Framework\Security\Service\RoleService;
use Codeages\Biz\Framework\Service\BaseService;

class RoleServiceImpl extends BaseService implements RoleService
{
    public function createRole($role)
    {
        return $this->getRoleDao()->create($role);
    }

    public function deleteRole($id)
    {
        $this->getRoleDao()->create($id);
    }

    public function updateRole($id, $role)
    {
        $this->getRoleDao()->update($id, $role);
    }

    protected function getRoleDao()
    {
        return $this->biz->dao('Security:RoleDao');
    }
}