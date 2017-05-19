<?php

namespace Codeages\Biz\Framework\Scheduler\Service;

interface SchedulerService
{
    public function schedule($job);

    public function execute();

    public function deleteJobByName($name);

    public function deleteJob($id);

    public function clearJobs();

    public function searchJobLogs($condition, $orderBy, $start, $limit);

    public function countJobLogs($condition);

    public function searchJobs($condition, $orderBy, $start, $limit);

    public function countJobs($condition);
}
