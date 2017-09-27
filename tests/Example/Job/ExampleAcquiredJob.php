<?php

namespace Tests\Example\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class ExampleAcquiredJob extends AbstractJob
{
    public function execute()
    {
        $i = 0;
        ++$i;
        sleep(3);
<<<<<<< HEAD
=======

>>>>>>> master
        return static::RETRY;
    }
}
