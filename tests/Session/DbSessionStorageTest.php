<?php

namespace Tests\Session;

use Codeages\Biz\Framework\Session\Storage\SessionStorage;
use Tests\IntegrationTestCase;

class DbSessionStorageTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->biz['session.options'] = [
            'max_life_time' => 1,
            'session_storage' => 'db',
        ];
    }

    public function testCreate()
    {
        $mockedSession = $this->mockSession();
        $session = $this->getSessionStorage()->save($mockedSession);

        $keys = array_keys($mockedSession);
        foreach ($keys as $key) {
            $this->assertEquals($mockedSession[$key], $session[$key]);
        }
    }

    public function testCreateWithSql()
    {
        $mockedSessionWithSql = $this->mockSessionWithSql();
        $session = $this->getSessionStorage()->save($mockedSessionWithSql);

        $keys = array_keys($mockedSessionWithSql);
        foreach ($keys as $key) {
            $this->assertEquals($mockedSessionWithSql[$key], $session[$key]);
        }
    }

    public function testUpdateSessionBySessId()
    {
        $mockedSession = $this->mockSession();
        $session = $this->getSessionStorage()->save($mockedSession);

        $session['sess_data'] = 'test';
        $updatedSession = $this->getSessionStorage()->save($session);
        $keys = array_keys($mockedSession);
        foreach ($keys as $key) {
            if (in_array($key, ['sess_data', 'sess_time'])) {
                continue;
            }
            $this->assertEquals($mockedSession[$key], $session[$key]);
        }

        $this->assertNotEquals($mockedSession['sess_data'], $updatedSession['sess_data']);
        $this->assertNotEmpty($updatedSession['sess_time']);
    }

    public function testUpdateSessionBySessIdWithSql()
    {
        $mockedSession = $this->mockSessionWithSql();
        $session = $this->getSessionStorage()->save($mockedSession);

        $session['sess_data'] = 'test';
        $updatedSession = $this->getSessionStorage()->save($session);
        $keys = array_keys($mockedSession);
        foreach ($keys as $key) {
            if (in_array($key, ['sess_data', 'sess_time'])) {
                continue;
            }
            $this->assertEquals($mockedSession[$key], $session[$key]);
        }

        $this->assertNotEquals($mockedSession['sess_data'], $updatedSession['sess_data']);
        $this->assertNotEmpty($updatedSession['sess_time']);
    }

    public function testDeleteSession()
    {
        $mockedSession = $this->mockSession();
        $session = $this->getSessionStorage()->save($mockedSession);
        $this->getSessionStorage()->delete($session['sess_id']);

        $deleteSession = $this->getSessionStorage()->get($session['sess_id']);
        $this->assertEmpty($deleteSession);
    }

    public function testDeleteSessionWithSql()
    {
        $mockedSession = $this->mockSessionWithSql();
        $session = $this->getSessionStorage()->save($mockedSession);
        $this->getSessionStorage()->delete($session['sess_id']);

        $deleteSession = $this->getSessionStorage()->get($session['sess_id']);
        $this->assertEmpty($deleteSession);
    }

    public function testGc()
    {
        $mockedSession = $this->mockSession();
        $mockedSession['sess_deadline'] = time() - 1;
        $this->getSessionStorage()->save($mockedSession);

        $this->getSessionStorage()->gc();
        $deleteSession = $this->getSessionStorage()->get($mockedSession['sess_id']);
        $this->assertEmpty($deleteSession);
    }

    public function testGcWithSql()
    {
        $mockedSession = $this->mockSessionWithSql();
        $mockedSession['sess_deadline'] = time() - 1;
        $this->getSessionStorage()->save($mockedSession);

        $this->getSessionStorage()->gc();
        $deleteSession = $this->getSessionStorage()->get($mockedSession['sess_id']);
        $this->assertEmpty($deleteSession);
    }

    protected function mockSession()
    {
        return [
            'sess_id' => 'sess'.rand(1000000, 9000000),
            'sess_data' => 'ababa',
            'sess_deadline' => time() + $this->biz['session.options']['max_life_time'],
        ];
    }

    protected function mockSessionWithSql()
    {
        return [
            'sess_id' => 'sess_'.rand(1000000, 9000000).'"1\' OR \'1\'=\'1"',
            'sess_data' => 'sql',
            'sess_deadline' => time() + $this->biz['session.options']['max_life_time'],
        ];
    }

    /**
     * @return SessionStorage
     */
    protected function getSessionStorage(): SessionStorage
    {
        return $this->biz['session.storage.db'];
    }
}
