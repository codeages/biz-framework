<?php

namespace Codeages\Biz\Framework\Security\Service;

interface UserService
{
    public function register($user);

    public function getUserByNickname($nickname);

    public function getUserByEmail($email);

    public function getUserByMobile($mobile);
}