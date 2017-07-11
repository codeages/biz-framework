<?php

namespace Codeages\Biz\Framework\Security\Service;

interface RoleService
{
    public function createRole($role);

    public function updateRole($id, $role);

    public function deleteRole($id);
}