<?php

namespace Codeages\Biz\Framework\Scheduler\Service;

interface SchedulerService
{
    const JOB_MEMORY_LIMIT = 209715200; //200MB

    public function register($job);

    public function execute();

    public function deleteJobByName($name);

    public function deleteJob($id);

    public function deleteJobFired($keepDays);

    public function findJobFiredsByJobId($jobId);

    public function findExecutingJobFiredByJobId($jobId);

    public function searchJobLogs($condition, $orderBy, $start, $limit);

    public function countJobLogs($condition);

    public function searchJobs($condition, $orderBy, $start, $limit);

    public function searchJobFireds($condition, $orderBy, $start, $limit);

    public function countJobs($condition);

    public function markTimeoutJobs();

    public function createErrorLog($jobFired, $message, $trace);

    public function createJobProcess($process);

    public function updateJobProcess($id, $process);
}
