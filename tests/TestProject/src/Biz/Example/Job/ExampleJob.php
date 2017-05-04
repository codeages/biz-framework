<?php

namespace TestProject\Biz\Example\Job;

use Codeages\Biz\Framework\Scheduler\Job\AbstractJob;

class ExampleJob extends AbstractJob
{
    public function execute()
    {
        $i = 0;
        $i++;
    }
}