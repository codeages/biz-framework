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

    public function testNewInstance_MissArguments_ThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new CurrentUser(array('id' => 1));
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

    public function testSet_NewKey()
    {
        $rawUser = $this->fakeUser();
        $user = new CurrentUser($rawUser);
        $user['new_key'] = 'new_value';

        $this->assertEquals($user['new_key'], 'new_value');
    }

    public function testSet_ResetOldKey_ThrowException()
    {
        $this->expectException(\LogicException::class);
        $rawUser = $this->fakeUser();
        $user = new CurrentUser($rawUser);
        $user['id'] = 2;
    }

    protected function fakeUser($fields = array())
    {
        return array_merge(array(
            'id' => 1,
            'username' => 'test_user',
            'login_client' => 'chrome',
            'login_ip' => '127.0.0.1',
        ), $fields);
    }
}
