<?php
namespace Tests\Queue;

use Tests\IntegrationTestCase;

class QueueBaseTestCase extends IntegrationTestCase
{
    const TEST_QUEUE = 'test_queue';

    protected function getQueueOptions()
    {
        return  array(
            'table' => 'biz_queue_job',
        );
    }

}