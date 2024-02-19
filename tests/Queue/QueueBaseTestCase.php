<?php

namespace Tests\Queue;

use Tests\Fixtures\QueueJob\ExampleFailedJob;
use Tests\Fixtures\QueueJob\ExampleFinishedJob;
use Tests\IntegrationTestCase;

class QueueBaseTestCase extends IntegrationTestCase
{
    const TEST_QUEUE = 'test_queue';

    protected function getQueueOptions()
    {
        return [
            'table' => 'biz_queue_job',
        ];
    }

    protected function createExampleFinishedJob(array $metadata = [])
    {
        $body = ['name' => 'example job'];

        return new ExampleFinishedJob($body, $metadata);
    }

    protected function createExampleFailedJob(array $metadata = [])
    {
        $body = ['name' => 'example job'];

        return new ExampleFailedJob($body, $metadata);
    }

    protected function createLock()
    {
        return $this->biz['lock.factory']->createLock('queue-for-phpunit');
    }
}
