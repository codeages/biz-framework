<?php

namespace Codeages\Biz\Framework\Scheduler\Checker;

class MisfireChecker implements JobChecker
{
    public function check($jobFired)
    {
        $now = time();
        $job = $jobFired['job'];
        $fireTime = $job['nextFireTime'];

        if (!empty($job['misfireThreshold']) && ($now - $fireTime) > $job['misfireThreshold']) {
            return $job['misfirePolicy'];
        }

        return static::EXECUTING;
    }
}