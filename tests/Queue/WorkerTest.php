<?php

namespace Tests\Queue;

use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;
use Codeages\Biz\Framework\Queue\JobFailer;
use Codeages\Biz\Framework\Queue\Worker;
use Monolog\Handler\TestHandler;
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

        $this->assertTrue($this->getTestLoggerHandler()->hasInfo('ExampleFinishedJob executed.'));
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

        $this->assertTrue($this->getTestLoggerHandler()->hasInfo('ExampleFailedJob executed.'));

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

        $worker = new Worker($queue, $failer, $this->createLock(), $this->biz['logger'], []);
        $worker->runNextJob();

        $this->assertTrue($this->getTestLoggerHandler()->hasInfo('ExampleFailedRetryJob executed.'));
        $this->assertTrue($this->getTestLoggerHandler()->hasWarning(sprintf("[Queue Worker - %s] Execute job #%d failed, drop it.", self::TEST_QUEUE, $job->getId())));
        $this->assertCount(0, $this->fetchAllFromDatabase($queueOptions['table'], ['queue' => self::TEST_QUEUE]));
    }

    public function testRunFailedRetryJobWhenTriesIsThree()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $job = new ExampleFailedRetryJob(['name' => 'example job']);
        $queue->push($job);
        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));
        $worker = new Worker($queue, $failer, $this->createLock(), $this->biz['logger'], ['tries' => 3]);

        for ($i = 1; $i <= 4; $i++) {
            $runJob = $worker->runNextJob();
            $this->assertNotNull($runJob);
            $this->assertEquals($job->getId(), $runJob->getId());
            $this->assertEquals($i, $runJob->getMetadata('executions'));
            if ($i == 4) {
                $record = sprintf("[Queue Worker - %s] Execute job #%d failed, drop it.", self::TEST_QUEUE, $job->getId());
            } else {
                $record = sprintf("[Queue Worker - %s] Execute job #%d failed, retry %d times.", self::TEST_QUEUE, $job->getId(), $i);
            }
            $this->assertTrue($this->getTestLoggerHandler()->hasWarning($record), $record);
        }

        $this->assertCount(0, $this->fetchAllFromDatabase($queueOptions['table'], ['queue' => self::TEST_QUEUE]));
    }

    /**
     * @return TestHandler
     */
    public function getTestLoggerHandler()
    {
        return $this->biz['logger.test_handler'];
    }
}
