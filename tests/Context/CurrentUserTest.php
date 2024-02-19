<?php

namespace Tests\Context;

use Codeages\Biz\Framework\Context\CurrentUser;
use PHPUnit\Framework\TestCase;

class CurrentUserTest extends TestCase
{
    public function testNewInstance()
    {
        $rawUser = $this->fakeUser();
        $user = new CurrentUser($rawUser);

        $this->assertInstanceOf('\Codeages\Biz\Framework\Context\CurrentUser', $user);
    }

    public function testNewInstanceMissArgumentsThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new CurrentUser(['id' => 1]);
    }

    public function testGet()
    {
        $rawUser = $this->fakeUser();
        $user = new CurrentUser($rawUser);
        $this->assertEquals($rawUser['id'], $user['id']);
        $this->assertEquals($rawUser['username'], $user['username']);
        $this->assertEquals($rawUser['login_client'], $user['login_client']);
        $this->assertEquals($rawUser['login_ip'], $user['login_ip']);
    }

    public function testSetNewKey()
    {
        $rawUser = $this->fakeUser();
        $user = new CurrentUser($rawUser);
        $user['new_key'] = 'new_value';

        $this->assertEquals($user['new_key'], 'new_value');
    }

    public function testSetResetOldKeyThrowException()
    {
        $this->expectException(\LogicException::class);
        $rawUser = $this->fakeUser();
        $user = new CurrentUser($rawUser);
        $user['id'] = 2;
    }

    protected function fakeUser($fields = [])
    {
        return array_merge([
            'id' => 1,
            'username' => 'test_user',
            'login_client' => 'chrome',
            'login_ip' => '127.0.0.1',
        ], $fields);
    }
}
