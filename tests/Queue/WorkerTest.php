<?php

namespace Tests\Queue;

use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;
use Codeages\Biz\Framework\Queue\JobFailer;
use Codeages\Biz\Framework\Queue\Worker;
use Tests\Fixtures\QueueJob\ExampleFailedJob;
use Tests\Fixtures\QueueJob\ExampleFailedRetryJob;
use Tests\Fixtures\QueueJob\ExampleFinishedJob;

class WorkerTest extends QueueBaseTestCase
{
    public function testRunFinishedJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = ['name' => 'example job'];
        $job = new ExampleFinishedJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = [
            'once' => true,
        ];

        $worker = new Worker($queue, $failer, $this->createLock(), $this->biz['logger'], $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo('ExampleFinishedJob executed.'));
        $this->assertCount(0, $this->fetchAllFromDatabase($queueOptions['table'], ['queue' => self::TEST_QUEUE]));
    }

    public function testRunFailedJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = ['name' => 'example job'];
        $job = new ExampleFailedJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = [
            'once' => true,
        ];

        $worker = new Worker($queue, $failer, $this->createLock(), $this->biz['logger'], $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo('ExampleFailedJob executed.'));

        $this->assertCount(0, $this->fetchAllFromDatabase($queueOptions['table'], ['queue' => self::TEST_QUEUE]));
        $this->assertCount(1, $this->fetchAllFromDatabase('biz_queue_failed_job', [
            'queue' => self::TEST_QUEUE,
            'reason' => 'ExampleFailedJob execute failed.',
        ]));
    }

    public function testRunFailedRetryJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = ['name' => 'example job'];
        $job = new ExampleFailedRetryJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = [
            'once' => true,
        ];

        $worker = new Worker($queue, $failer, $this->createLock(), $this->biz['logger'], $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo('ExampleFailedRetryJob executed.'));

        $this->assertCount(0, $this->fetchAllFromDatabase($queueOptions['table'], [
            'queue' => self::TEST_QUEUE,
            'executions' => 1,
            'reserved_time' => 0,
            'expired_time' => 0,
        ]));
    }
}
