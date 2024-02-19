<?php

namespace Tests\Queue;

use Tests\Fixtures\QueueJob\ExampleFinishedJob;
use Tests\IntegrationTestCase;

class QueueServiceTest extends IntegrationTestCase
{
    public function testPush()
    {
        $job = new ExampleFinishedJob();
        $this->getQueueService()->pushJob($job);
        $this->assertGreaterThan(0, $job->getId());
    }

    public function testGetFailedJob()
    {
        $failedJob = $this->getQueueService()->getFailedJob(1);
        $this->assertNull($failedJob);
    }

    public function testCountFailedJobs()
    {
        $count = $this->getQueueService()->countFailedJobs([]);
        $this->assertEquals(0, $count);
    }

    public function testSearchFailedJobs()
    {
        $failedJobs = $this->getQueueService()->searchFailedJobs([], ['failed_time' => 'desc'], 0, 10);
        $this->assertEquals(0, count($failedJobs));
    }

    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }
}
