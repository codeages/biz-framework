<?php

namespace Codeages\Biz\Framework\Scheduler\Service;

interface SchedulerService
{
    public function schedule($job);

    public function execute();

    public function deleteJobByPoolAndName($pool, $name);

    public function deleteJob($id);

    public function clearJobs();
}
