<?php

namespace Codeages\Biz\Framework\Scheduler\Checker;

class ExecutingChecker extends AbstractJobChecker
{
    public function check($jobFired)
    {
        $jobFireds = $this->getSchedulerService()->findJobFiredsByJobId($jobFired['job_id']);
        foreach ($jobFireds as $item) {
            if ($item['id'] == $jobFired['id']) {
                continue;
            }

            if (static::EXECUTING == $item['status']) {
                return 'ignore';
            }
        }

        return static::EXECUTING;
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}