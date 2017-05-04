<?php

namespace Codeages\Biz\Framework\Scheduler\Processor;

class MisFireChecker implements JobChecker
{
    public function check($firedJob)
    {
        $now = time();
        $jobDetail = $firedJob['jobDetail'];
        $fireTime = $jobDetail['nextFireTime'];

        if (!empty($jobDetail['misfireThreshold']) && ($now - $fireTime) > $jobDetail['misfireThreshold']) {
            switch ($jobDetail['misfirePolicy']) {
                case '':
                    return $jobDetail['misfirePolicy'];
                    break;
                case '':
                    return $jobDetail['misfirePolicy'];
                    break;
                case '':
                    return $jobDetail['misfirePolicy'];
                    break;
            }
        }

        return 'executing';
    }
}