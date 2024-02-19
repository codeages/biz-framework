<?php

namespace Tests\Queue\Driver;

use Codeages\Biz\Framework\Queue\Driver\SyncQueue;
use Tests\Queue\QueueBaseTestCase;

class SyncQueueTest extends QueueBaseTestCase
{
    public function testPushFinishedJob()
    {
        $queue = new SyncQueue(self::TEST_QUEUE, $this->biz, $this->biz['queue.failer']);

        $job = $this->createExampleFinishedJob();
        $queue->push($job);

        $this->assertGreaterThan(0, $job->getId());
        $this->assertTrue($this->biz['logger.test_handler']->hasInfo('ExampleFinishedJob executed.'));
    }

    public function testPushFailedJob()
    {
        $queue = new SyncQueue(self::TEST_QUEUE, $this->biz, $this->biz['queue.failer']);

        $job = $this->createExampleFailedJob();
        $queue->push($job);

        $this->assertGreaterThan(0, $job->getId());
        $this->assertTrue($this->biz['logger.test_handler']->hasInfo('ExampleFailedJob executed.'));

        $this->assertCount(1, $this->fetchAllFromDatabase('biz_queue_failed_job', [
            'queue' => self::TEST_QUEUE,
            'reason' => 'ExampleFailedJob execute failed.',
        ]));
    }
}
