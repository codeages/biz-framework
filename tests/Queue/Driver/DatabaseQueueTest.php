<?php
namespace Tests\Queue;

use Tests\IntegrationTestCase;
use Codeages\Biz\Framework\Queue\Driver\DatabaseQueue;


class DatabaseQueueTest extends IntegrationTestCase
{
    public function testPop_WithNewJobs()
    {
        $this->seed('Tests\\Queue\\JobSeeder', true);

        $queue = new DatabaseQueue($this->biz);
        $job = $queue->pop();

        

        var_dump($job);

    }
}