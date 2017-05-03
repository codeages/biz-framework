<?php

namespace Codeages\Biz\Framework\Scheduler\Service\Impl;

use Codeages\Biz\Framework\Scheduler\Processor\CheckerChain;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\Framework\Service\BaseService;

class SchedulerServiceImpl extends BaseService implements SchedulerService
{
    public function schedule($jobDetail)
    {
        // TODO: Implement schedule() method.
    }

    public function run()
    {
        $jobDetail = $this->checkAndFire();
        if (empty($jobDetail)) {
            return;
        }

        $jobInstance = $this->createJobInstance($jobDetail);
        $this->getJobPool()->execute($jobInstance);
    }

    protected function checkAndFire()
    {
        $job = $this->getJobDao()->getWaitingJobByLessThanFireTime(strtotime('+1 minutes'));
        if (empty($job)) {
            return;
        }

        $result = new CheckerChain($this->biz)->check($job);
        if ($result == 'success') {
            return $this->getJobDao()->update($job['id'], array('status' => 'executing'));
        }

        return $this->checkAndFire();
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }

    protected function createJobInstance($jobDetail)
    {
        $class = $jobDetail['class'];
        return new $class($jobDetail['params']);
    }

    protected function getJobPool()
    {
        return $this->biz['scheduler.job.pool'];
    }
}