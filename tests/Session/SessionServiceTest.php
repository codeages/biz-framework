<?php

namespace Tests;

class SessionServiceTest extends IntegrationTestCase
{
    public function testCreate()
    {
        $mockedSession = $this->mockSession();
        $session = $this->getSessionService()->createSession($mockedSession);

        $keys = array_keys($mockedSession);
        foreach ($keys as $key) {
            $this->assertEquals($mockedSession[$key],$session[$key]);
        }
    }

    public function testUpdateSessionBySessId()
    {
        $mockedSession = $this->mockSession();
        $session = $this->getSessionService()->createSession($mockedSession);

        sleep(1);

        $session['sess_data'] = 'test';
        $updatedSession = $this->getSessionService()->updateSessionBySessId($session['sess_id'], $session);

        $keys = array_keys($mockedSession);
        foreach ($keys as $key) {
            if (in_array($key, array('sess_data', 'sess_time'))) {
                continue;
            }
            $this->assertEquals($mockedSession[$key],$updatedSession[$key]);
        }

        $this->assertNotEquals($mockedSession['sess_data'],$updatedSession['sess_data']);
        $this->assertNotEmpty($updatedSession['sess_time']);
    }

    public function testDeleteSession()
    {
        $mockedSession = $this->mockSession();
        $session = $this->getSessionService()->createSession($mockedSession);
        $this->getSessionService()->deleteSessionBySessId($session['sess_id']);

        $deleteSession = $this->getSessionService()->getSessionBySessId($session['sess_id']);
        $this->assertEmpty($deleteSession);
    }

    public function testGc()
    {
        $mockedSession = $this->mockSession();
        $this->getSessionService()->createSession($mockedSession);

        sleep(2);

        $this->getSessionService()->gc();
        $deleteSession = $this->getSessionService()->getSessionBySessId($mockedSession['sess_id']);
        $this->assertEmpty($deleteSession);
    }

    protected function mockSession()
    {
        return array(
            'sess_id' => 'sess'.rand(1000000,9000000),
            'sess_data' => 'ababa',
            'sess_deadline' => time() + 1,
        );
    }

    protected function getSessionService()
    {
        return $this->biz->service('Session:SessionService');
    }
}