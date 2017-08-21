<?php
namespace Tests\Queue;

use Tests\IntegrationTestCase;
use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;
use Tests\Fixtures\QueueJob\ExampleJob1;
use PHPUnit\DbUnit\TestCaseTrait;

class DatabaseQueueTest extends IntegrationTestCase
{
    public function testPush_PushExampleJob_JobInserted()
    {
        $queue = new DatabaseQueue('default', $this->biz);
        $job = new ExampleJob1(array('name' => 'example job 1'));
        $queue->push($job);
        // $this->assertGreaterThan(0, $job->getId());

        $this->assertInDatabase('biz_queue_job', array('queue' => 'default'));
    }

    // public function testPop_WithNewJobs()
    // {
    //     $this->seed('Tests\\Queue\\JobSeeder', true);

    //     $queue = new DatabaseQueue($this->biz);
    //     $job = $queue->push();

    //     $this->assertInstanceOf('Tests\\Fixtures\\QueueJob\\ExampleJob1', $job);
    // }
}