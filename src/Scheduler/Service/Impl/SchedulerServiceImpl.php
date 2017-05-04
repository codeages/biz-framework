<?php

namespace Codeages\Biz\Framework\Scheduler\Service\Impl;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Util\Lock;

class SchedulerServiceImpl extends BaseService implements SchedulerService
{
    public function schedule($jobDetail)
    {
        // TODO: Implement schedule() method.
    }

    public function run()
    {
        $this->acquiredWaitingJobs();

        $fireJob = $this->triggerJob();
        if (empty($fireJob)) {
            return;
        }

        $jobInstance = $this->createJobInstance($fireJob);
        $this->getJobPool()->execute($jobInstance);
    }

    protected function triggerJob()
    {
        $lock = new Lock($this->biz);
        $lockName = 'scheduler.job.trigger';
        try {
            $lock->get($lockName, 20);
            $this->biz['db']->beginTransaction();

            $jobDetail = $this->getTriggeredJob();

            $this->biz['db']->commit();
            $lock->release($lockName);
            return $jobDetail;
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            $lock->release($lockName);
            throw new ServiceException($e);
        }
    }

    protected function getTriggeredJob()
    {
        $createdFireJob = $this->getFireJobDao()->getByStatus('created');
        if (empty($createdFireJob)) {
            return;
        }

        $jobDetail = $this->getJobDao()->get($createdFireJob['jobDetailId']);
        $createdFireJob['jobDetail'] = $jobDetail;
        $result =  $this->getCheckerChain()->check($createdFireJob);

        $fireJob = $this->getFireJobDao()->update($createdFireJob['id'], array('status' => $result));

        $fireJob['jobDetail'] = $jobDetail;
        $this->triggerNextExecuteJob($fireJob);

        if ($fireJob['status'] == 'executing') {
            return $fireJob;
        }

        return $this->getTriggeredJob();
    }

    protected function triggerNextExecuteJob($fireJob)
    {
        // TODO:
    }

    protected function acquiredWaitingJobs()
    {
        $lock = new Lock($this->biz);
        $lockName = 'scheduler.job.acquired_waiting_jobs';

        try {
            $lock->get($lockName, 20);
            $this->biz['db']->beginTransaction();

            $jobDetails = $this->getJobDao()->findWaitingJobsByLessThanFireTime(strtotime('+1 minutes'));

            foreach ($jobDetails as $jobDetail) {
                $this->createFireJob($jobDetail);
            }

            $this->biz['db']->commit();
            $lock->release($lockName);
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            $lock->release($lockName);
            throw new ServiceException($e);
        }
    }

    protected function createFireJob($jobDetail)
    {
        $jobDetail = $this->getJobDao()->update($jobDetail['id'], array('status' => 'acquired'));

        $fireJob = array(
            'jobDetailId' => $jobDetail['id'],
            'firedTime' => $jobDetail['nextFireTime'],
        );
        $this->getFireJobDao()->create($fireJob);

        $this->createJobLog($jobDetail);
    }

    protected function createJobLog($jobDetail)
    {
        // TODO:
        $log = array();
        $this->biz->service('Scheduler:JobLogService')->create($log);
    }

    protected function createJobInstance($jobDetail)
    {
        $class = $jobDetail['class'];
        return new $class($jobDetail['params']);
    }

    protected function getCheckerChain()
    {
        return $this->biz['scheduler.job.checker_chain'];
    }

    protected function getFireJobDao()
    {
        return $this->biz->dao('Scheduler:FireJobDao');
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }

    protected function getJobPool()
    {
        return $this->biz['scheduler.job.pool'];
    }
}