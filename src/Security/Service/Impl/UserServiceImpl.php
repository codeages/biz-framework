<?php

namespace Codeages\Biz\Framework\Security\Service\Impl;

use Codeages\Biz\Framework\Security\Service\UserService;
use Codeages\Biz\Framework\Service\BaseService;

class UserServiceImpl extends BaseService implements UserService
{
    public function register($user)
    {
        return $this->getUserDao()->create($user);
    }

    public function getUserByNickname($nickname)
    {
        return $this->getUserDao()->getByNickname($nickname);
    }

    public function getUserByEmail($email)
    {
        return $this->getUserDao()->getByEmail($email);
    }

    public function getUserByMobile($mobile)
    {
        return $this->getUserDao()->getByMobile($mobile);
    }

    protected function getUserDao()
    {
        return $this->biz->dao('Security:UserDao');
    }
}