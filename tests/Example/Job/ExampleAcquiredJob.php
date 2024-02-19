<?php

namespace Tests\Example\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ExampleAcquiredJob extends AbstractJob
{
    public function execute()
    {
        return static::RETRY;
    }
}
