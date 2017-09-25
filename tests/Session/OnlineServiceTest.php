<?php

namespace Tests;

class SessionServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->biz['user'] = array(
            'id' => 1
        );
    }


    public function testCountLogin()
    {
        $mockedSession = $this->mockOnline();
        $this->getOnlineService()->createOnline($mockedSession);
        $count = $this->getOnlineService()->countLogined(time()-400);

        $this->assertEquals(1, $count);
    }

    public function testCountTotal()
    {
        $mockedSession = $this->mockOnline();
        $this->biz['user'] = array(
            'id' => 0
        );
        $this->getOnlineService()->createOnline($mockedSession);

        $count = $this->getOnlineService()->countLogined(time()-400);
        $this->assertEquals(1, $count);


        $count = $this->getOnlineService()->countOnline(time()-400);
        $this->assertEquals(1, $count);
    }

    protected function mockOnline()
    {
        return array(
            'sess_id' => 'sess'.rand(1000000,9000000),
            'user_id' => 1,
            'user_agent' => 'xxfafaafasfasf',
            'access_time' => time(),
            'created_time' => time(),
            'lifetime' => 1,
            'source' => 'web',
        );
    }

    protected function getOnlineService()
    {
        return $this->biz->service('Session:OnlineService');
    }
}
