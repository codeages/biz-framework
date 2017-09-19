<?php

namespace Codeages\Biz\Framework\Scheduler\Service;

interface SchedulerService
{
    public function register($job);

    public function execute();

    public function deleteJobByName($name);

    public function deleteJob($id);

    public function findJobFiredsByJobId($jobId);

    public function searchJobLogs($condition, $orderBy, $start, $limit);

    public function countJobLogs($condition);

    public function searchJobs($condition, $orderBy, $start, $limit);

    public function countJobs($condition);
}
