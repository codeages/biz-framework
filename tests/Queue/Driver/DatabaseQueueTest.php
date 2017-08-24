<?php

namespace Tests\Queue\Driver;

use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;
use Tests\Fixtures\QueueJob\ExampleFinishedJob;
use Tests\Queue\QueueBaseTestCase;

class DatabaseQueueTest extends QueueBaseTestCase
{
    public function testPush_PushExampleJob_JobInserted()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $body = array('name' => 'example job');
        $job = new ExampleFinishedJob($body);
        $queue->push($job);

        $this->assertGreaterThan(0, $job->getId());

        $savedJob = $this->fetchFromDatabase($queueOptions['table'], array(
            'id' => $job->getId(),
        ));

        $this->assertEquals($queue->getName(), $savedJob['queue']);
        $this->assertEquals(get_class($job), $savedJob['class']);
        $this->assertEquals(ExampleFinishedJob::DEFAULT_TIMEOUT, $savedJob['timeout']);
        $this->assertEquals(ExampleFinishedJob::DEFAULT_PRIORITY, $savedJob['priority']);

    }

    public function testPop()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $body = array('name' => 'example job');
        $job = new ExampleFinishedJob($body);

        $queue->push($job);
        $job = $queue->pop();

        $this->assertEquals($body, $job->getBody());
        $this->assertGreaterThan(0, $job->getId());
    }

    public function testDelete()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $body = array('name' => 'example job');
        $job = new ExampleFinishedJob($body);
        $queue->push($job);

        $queue->delete($job);

        $this->assertNotInDatabase($queueOptions['table'], array('queue' => self::TEST_QUEUE));
    }

    public function testRelease()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $body = array('name' => 'example job');
        $job = new ExampleFinishedJob($body);
        $queue->push($job);

        $job = $queue->pop();
        $queue->release($job);

        $this->assertInDatabase($queueOptions['table'], array(
            'queue' => self::TEST_QUEUE,
            'executions' => 1,
            'reserved_time' => 0,
            'expired_time' => 0,
        ));
    }

    // public function testPop_WithNewJobs()
    // {
    //     $this->seed('Tests\\Queue\\JobSeeder', true);

    //     $queue = new DatabaseQueue($this->biz);
    //     $job = $queue->push();

    //     $this->assertInstanceOf('Tests\\Fixtures\\QueueJob\\ExampleFinishedJob', $job);
    // }
}
