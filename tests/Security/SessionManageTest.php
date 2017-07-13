<?php

namespace Tests;

class SessionManageTest extends IntegrationTestCase
{
    public function testCreateSession()
    {
        $mockedSession = array(
            'sess_id' => 'fasafqrqwxxfasf',
            'sess_user_id' => 1,
            'sess_data' => 'dqeqdass',
            'sess_time' => time(),
            'sess_lifetime' => 86400,
        );
        $session = $this->getSessionManage()->createSession($mockedSession);

        $this->assertEquals($mockedSession['sess_id'], $session['sess_id']);
        $this->assertEquals($mockedSession['sess_user_id'], $session['sess_user_id']);
        $this->assertEquals($mockedSession['sess_data'], $session['sess_data']);
        $this->assertEquals($mockedSession['sess_time'], $session['sess_time']);
        $this->assertEquals($mockedSession['sess_lifetime'], $session['sess_lifetime']);

        $session = $this->getSessionManage()->getSessionBySessionId($session['sess_id']);
        $this->assertEquals($mockedSession['sess_id'], $session['sess_id']);
        $this->assertEquals($mockedSession['sess_user_id'], $session['sess_user_id']);
        $this->assertEquals($mockedSession['sess_data'], $session['sess_data']);
        $this->assertEquals($mockedSession['sess_time'], $session['sess_time']);
        $this->assertEquals($mockedSession['sess_lifetime'], $session['sess_lifetime']);
    }

    public function testDeleteSession()
    {
        $session = $this->mockSession();

        $this->getSessionManage()->deleteSessionBySessionId($session['sess_id']);
        $session = $this->getSessionManage()->getSessionBySessionId($session['sess_id']);
        $this->assertEmpty($session);
    }

    public function testDeleteInvalidSession()
    {
        $session = $this->mockSession();
        sleep(2);
        $this->getSessionManage()->deleteInvalidSession(time());

        $session = $this->getSessionManage()->getSessionBySessionId($session['sess_id']);
        $this->assertEmpty($session);
    }

    public function testLoginCount()
    {
        $this->mockSession();

        $mockedSession = array(
            'sess_id' => 'rrqwfsfsdvsf',
            'sess_user_id' => 0,
            'sess_data' => 'dqeqdass',
            'sess_time' => time(),
            'sess_lifetime' => 86400,
        );
        $this->getSessionManage()->createSession($mockedSession);

        $count = $this->getSessionManage()->countLogin(time()-5);
        $this->assertEquals(1, $count);

        $count = $this->getSessionManage()->countOnline(time()-5);
        $this->assertEquals(2, $count);
    }

    protected function getSessionManage()
    {
        return $this->biz->service('Security:SessionManage');
    }

    protected function mockSession()
    {
        $mockedSession = array(
            'sess_id' => 'fasafqrqwxxfasf',
            'sess_user_id' => 1,
            'sess_data' => 'dqeqdass',
            'sess_time' => time(),
            'sess_lifetime' => 86400,
        );
        return $this->getSessionManage()->createSession($mockedSession);
    }
}