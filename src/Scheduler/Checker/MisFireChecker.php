<?php

namespace Codeages\Biz\Framework\Scheduler\Checker;

class MisFireChecker implements JobChecker
{
    public function check($jobFired)
    {
        $now = time();
        $jobDetail = $jobFired['jobDetail'];
        $fireTime = $jobDetail['nextFireTime'];

        if (!empty($jobDetail['misfireThreshold']) && ($now - $fireTime) > $jobDetail['misfireThreshold']) {
            return $jobDetail['misfirePolicy'];
        }

        return static::EXECUTING;
    }
}