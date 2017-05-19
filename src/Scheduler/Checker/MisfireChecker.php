<?php

namespace Codeages\Biz\Framework\Scheduler\Checker;

class MisfireChecker extends AbstractJobChecker
{
    public function check($jobFired)
    {
        $now = time();
        $job = $jobFired['job'];
        $fireTime = $job['next_fire_time'];

        if (!empty($job['misfire_threshold']) && ($now - $fireTime) > $job['misfire_threshold']) {
            return $job['misfire_policy'];
        }

        return static::EXECUTING;
    }
}