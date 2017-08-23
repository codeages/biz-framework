<?php
namespace Tests\Queue;

use Tests\IntegrationTestCase;
use Codeages\Biz\Framework\Queue\Worker;
use Codeages\Biz\Framework\Queue\JobFailer;
use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;
use Tests\Fixtures\QueueJob\ExampleFinishedJob;
use Tests\Fixtures\QueueJob\ExampleFailedJob;

class WorkerTest extends QueueBaseTestCase
{
    public function testRun_FinishedJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = array('name' => 'example job 1');
        $job = new ExampleFinishedJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = array(
            'once' => true,
        );

        $worker = new Worker($queue, $failer, $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo("ExampleFinishedJob executed."));
        $this->assertNotInDatabase($queueOptions['table'], array('queue' => self::TEST_QUEUE));
    }

    public function testRun_FailedJob()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);
        $body = array('name' => 'example job 1');
        $job = new ExampleFailedJob($body);
        $queue->push($job);

        $failer = new JobFailer($this->biz->dao('Queue:FailedJobDao'));

        $options = array(
            'once' => true,
        );

        $worker = new Worker($queue, $failer, $options);
        $worker->runNextJob();

        $this->assertTrue($this->biz['logger.test_handler']->hasInfo("ExampleFailedJob executed."));
        $this->assertNotInDatabase($queueOptions['table'], array('queue' => self::TEST_QUEUE));
    }
}