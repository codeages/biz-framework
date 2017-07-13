<?php

namespace Tests;

use Codeages\Biz\Framework\Security\Job\SessionTimeoutJob;

class SessionManagerTest extends IntegrationTestCase
{
    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateJobWithoutClass()
    {
        $mockedSession = array(
            'sess_user_id' => 1,
        );
        $this->getSessionManager()->createSession($mockedSession);
    }

    public function testCreateSession()
    {
        $mockedSession = array(
            'sess_user_id' => 1,
            'sess_data' => 'dqeqdass',
        );
        $session = $this->getSessionManager()->createSession($mockedSession);

        $this->assertNotEmpty($session['sess_id']);
        $this->assertNotEmpty($session['sess_time']);
        $this->assertNotEmpty($session['sess_lifetime']);
        $this->assertNotEmpty($session['created_time']);
        $this->assertNotEmpty($session['updated_time']);
        $this->assertNotEmpty($session['sess_id']);

        $this->assertEquals($mockedSession['sess_user_id'], $session['sess_user_id']);
        $this->assertEquals($mockedSession['sess_data'], $session['sess_data']);
        $this->assertEquals($this->biz['session.manager.timeout.default'], $session['sess_lifetime']);

        $session = $this->getSessionManager()->getSessionBySessionId($session['sess_id']);
        $this->assertNotEmpty($session['sess_id']);
        $this->assertNotEmpty($session['sess_time']);
        $this->assertNotEmpty($session['sess_lifetime']);
        $this->assertNotEmpty($session['created_time']);
        $this->assertNotEmpty($session['updated_time']);
        $this->assertNotEmpty($session['sess_id']);
        $this->assertEquals($mockedSession['sess_user_id'], $session['sess_user_id']);
        $this->assertEquals($mockedSession['sess_data'], $session['sess_data']);
        $this->assertEquals($this->biz['session.manager.timeout.default'], $session['sess_lifetime']);


        $mockedSession = array(
            'sess_id' => 'xxxxxxx',
            'sess_user_id' => 1,
            'sess_data' => 'dqeqdass',
        );
        $session = $this->getSessionManager()->createSession($mockedSession);
        $this->assertEquals($mockedSession['sess_id'], $session['sess_id']);
    }

    public function testDeleteSession()
    {
        $session = $this->mockSession();

        $this->getSessionManager()->deleteSessionBySessionId($session['sess_id']);
        $session = $this->getSessionManager()->getSessionBySessionId($session['sess_id']);
        $this->assertEmpty($session);
    }

    public function testDeleteInvalidSession()
    {
        $session = $this->mockSession();
        sleep(2);
        $this->getSessionManager()->deleteInvalidSessions(time());

        $session = $this->getSessionManager()->getSessionBySessionId($session['sess_id']);
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
        $this->getSessionManager()->createSession($mockedSession);

        $count = $this->getSessionManager()->countLogin(time()-5);
        $this->assertEquals(1, $count);

        $count = $this->getSessionManager()->countOnline(time()-5);
        $this->assertEquals(2, $count);
    }

    public function testSessionTimeoutJob()
    {
        $this->biz['session.manager.timeout.default'] = 1;

        $mockedSession = array(
            'sess_id' => 'rrqwfsfsdvsf',
            'sess_user_id' => 1,
            'sess_data' => 'dqeqdass',
        );
        $session = $this->getSessionManager()->createSession($mockedSession);

        sleep(2);
        
        $job = new SessionTimeoutJob(array(), $this->biz);
        $job->execute();

        $session = $this->getSessionManager()->getSessionBySessionId($session['sess_id']);
        $this->assertEmpty($session);
    }

    public function testRefresh()
    {
        $time = time();
        $mockedSession = array(
            'sess_id' => 'rrqwfsfsdvsf',
            'sess_user_id' => 0,
            'sess_data' => 'dqeqdass',
            'sess_time' => $time,
            'sess_lifetime' => 86400,
        );
        $session = $this->getSessionManager()->createSession($mockedSession);
        sleep(2);
        $this->getSessionManager()->refresh($session['sess_id'], 'xxxxxx');
        $session = $this->getSessionManager()->getSessionBySessionId($session['sess_id']);
        $this->assertEquals('xxxxxx', $session['sess_data']);
        $this->assertNotEquals($time, $session['sess_time']);
    }

    protected function getSessionManager()
    {
        return $this->biz->service('Security:SessionManager');
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
        return $this->getSessionManager()->createSession($mockedSession);
    }
}