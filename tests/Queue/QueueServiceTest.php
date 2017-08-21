<?php
namespace Tests\Queue;

use Codeages\Biz\Framework\TargetLog\Service\TargetlogService;
use Tests\IntegrationTestCase;
use Tests\Fixtures\QueueJob\ExampleJob1;

class QueueServiceTest extends IntegrationTestCase
{
    public function testPushJob()
    {
        $this->biz['queue.connection.default'] = function ($biz) {
            return $biz['queue.connection.sync'];
        };

        $body = array('name' => 'example 1');
        $job = new ExampleJob1($body);
        $job = $this->getQueueService()->pushJob($job);

        $this->assertEquals($body, $job->getBody());
    }

    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }
}