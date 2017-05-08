<?php

namespace Codeages\Biz\Framework\Scheduler\Service;

interface SchedulerService
{
    public function schedule($jobDetail);

    public function run();
}
