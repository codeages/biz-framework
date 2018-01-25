<?php

namespace Tests;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Webpatser\Uuid\Uuid;

class SchedulerServiceTest extends IntegrationTestCase
{
    public function testCreateJobProcess()
    {
        $uuid = str_replace('-', '', Uuid::generate(4));
        $process = array(
            'process_id' => $uuid,
            'start_time' => 10000,
            'end_time' => 12000,
            'cost_time' => 2000,
            'peak_memory' => 30000,
        );

        $result = $this->getSchedulerService()->createJobProcess($process);
        $this->assertEquals(10000, $result['start_time']);
        $this->assertEquals($uuid, $result['process_id']);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
