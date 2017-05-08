<?php

namespace Codeages\Biz\Framework\Scheduler\Checker;

class MissFireChecker implements JobChecker
{
    public function check($firedJob)
    {
        $now = time();
        $jobDetail = $firedJob['jobDetail'];
        $fireTime = $jobDetail['nextFireTime'];

        if (!empty($jobDetail['misfireThreshold']) && ($now - $fireTime) > $jobDetail['misfireThreshold']) {
            return $jobDetail['misfirePolicy'];
        }

        return static::EXECUTING;
    }
}