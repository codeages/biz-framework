<?php

namespace Codeages\Biz\Framework\Scheduler\Service;

interface SchedulerService
{
    public function scheduler($trigger, $jobDetail);

    public function run();
}
