<?php
namespace Tests\Queue;

use Tests\IntegrationTestCase;
use Tests\Fixtures\QueueJob\ExampleFinishedJob;

class QueueServiceTest extends IntegrationTestCase
{

    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }
}