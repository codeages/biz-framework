<?php

namespace Tests\Session;

use Codeages\Biz\Framework\Session\Service\SessionService;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\IntegrationTestCase;
use Tests\Session\Example\ExampleSessionStorage;

class SessionServiceTest extends IntegrationTestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->biz['session.storage.example'] = function () {
            return new ExampleSessionStorage($this->biz);
        };
        $this->biz['session.options'] = [
            'max_life_time' => 1,
            'session_storage' => 'example',
        ];
    }

    public function testSaveSession()
    {
        $session = $this->getSessionService()->saveSession(['sess_id' => 'test', 'sess_data' => 'test']);

        $this->assertEquals('test', $session['sess_data']);
        $this->assertEquals('test', $session['sess_data']);
        $this->assertGreaterThanOrEqual(time(), $session['sess_deadline']);
    }

    public function testDeleteSessionBySessId()
    {
        $this->assertTrue($this->getSessionService()->deleteSessionBySessId('test'));
    }

    public function testGetSessionBySessId()
    {
        $example = new ExampleSessionStorage($this->biz);

        $session = $this->getSessionService()->getSessionBySessId('sess_id');
        $this->assertEquals($example->get('sess_id'), $session);
    }

    public function testGc()
    {
        $this->assertTrue($this->getSessionService()->gc());
    }

    /**
     * @return SessionService
     */
    protected function getSessionService()
    {
        return $this->biz->service('Session:SessionService');
    }
}
