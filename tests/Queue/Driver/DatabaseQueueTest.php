<?php
namespace Tests\Queue;

use Tests\IntegrationTestCase;
use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;
use Tests\Fixtures\QueueJob\ExampleJob1;
use PHPUnit\DbUnit\TestCaseTrait;

class DatabaseQueueTest extends IntegrationTestCase
{
    const TEST_QUEUE = 'test_queue';

    public function testPush_PushExampleJob_JobInserted()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $body = array('name' => 'example job 1');
        $job = new ExampleJob1($body);
        $queue->push($job);
        
        $this->assertGreaterThan(0, $job->getId());
        $this->assertInDatabase($queueOptions['table'], array('queue' => self::TEST_QUEUE, 'class' => get_class($job)));
    }

    public function testPop()
    {
        $queueOptions = $this->getQueueOptions();
        $queue = new DatabaseQueue(self::TEST_QUEUE, $this->biz, $queueOptions);

        $body = array('name' => 'example job 1');
        $job = new ExampleJob1($body);

        $queue->push($job);
        $job = $queue->pop();

        $this->assertEquals($body, $job->getBody());
        $this->assertGreaterThan(0, $job->getId());
    }

    protected function getQueueOptions()
    {
        return  array(
            'table' => 'biz_queue_job',
        );
    }


    // public function testPop_WithNewJobs()
    // {
    //     $this->seed('Tests\\Queue\\JobSeeder', true);

    //     $queue = new DatabaseQueue($this->biz);
    //     $job = $queue->push();

    //     $this->assertInstanceOf('Tests\\Fixtures\\QueueJob\\ExampleJob1', $job);
    // }
}