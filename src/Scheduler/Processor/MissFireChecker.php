<?php

namespace Codeages\Biz\Framework\Scheduler\Processor;

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

        return 'executing';
    }
}