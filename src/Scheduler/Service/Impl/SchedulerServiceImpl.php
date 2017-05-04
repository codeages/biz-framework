<?php

namespace Codeages\Biz\Framework\Scheduler\Service\Impl;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Util\Lock;
use Cron\CronExpression;

class SchedulerServiceImpl extends BaseService implements SchedulerService
{
    public function create($jobDetail)
    {
        $jobDetail['nextFireTime'] = $this->getNextRunTime($jobDetail['expression']);
        $jobDetail = $this->getJobDao()->create($jobDetail);
        $this->createJobLog($jobDetail, 'created');
        return $jobDetail;
    }

    public function run()
    {
        $this->acquiredWaitingJobs();
        $firedJob = $this->triggerJob();
        if (empty($firedJob)) {
            return;
        }

        $jobInstance = $this->createJobInstance($firedJob);
        $result = $this->getJobPool()->execute($jobInstance);
        $this->createJobLog($firedJob['jobDetail'], $result);
    }

    protected function getNextRunTime($expression)
    {
        $cron = CronExpression::factory($expression);
        return strtotime($cron->getNextRunDate()->format('Y-m-d H:i:s'));
    }

    protected function triggerJob()
    {
        $lock = new Lock($this->biz);
        $lockName = 'scheduler.job.trigger';
        try {
            $lock->get($lockName, 20);
            $this->biz['db']->beginTransaction();

            $firedJob = $this->getTriggeredJob();

            $this->biz['db']->commit();
            $lock->release($lockName);

            return $firedJob;
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            $lock->release($lockName);
            throw new ServiceException($e);
        }
    }

    protected function getTriggeredJob()
    {
        $createdFireJob = $this->getFiredJobDao()->getByStatus('created');
        if (empty($createdFireJob)) {
            return;
        }

        $jobDetail = $this->getJobDao()->get($createdFireJob['jobDetailId']);
        $createdFireJob['jobDetail'] = $jobDetail;
        $result =  $this->getCheckerChain()->check($createdFireJob);

        $firedJob = $this->getFiredJobDao()->update($createdFireJob['id'], array('status' => $result));
        $this->createJobLog($jobDetail, $result);

        $firedJob['jobDetail'] = $jobDetail;
        $this->updateNextFireTime($firedJob);

        $this->createJobLog($jobDetail, $firedJob['status']);

        if ($firedJob['status'] == 'executing') {
            return $firedJob;
        }

        return $this->getTriggeredJob();
    }

    protected function updateNextFireTime($fireJob)
    {
        $jobDetail = $fireJob['jobDetail'];
        $fields = array(
            'status' => 'waiting',
            'preFireTime' => $jobDetail['nextFireTime'],
            'nextFireTime' => $this->getNextRunTime($jobDetail['expression'])
        );
        $this->getJobDao()->update($jobDetail['id'], $fields);
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
        $this->getFiredJobDao()->create($fireJob);
        $this->createJobLog($jobDetail, 'acquired');
    }

    protected function createJobLog($jobDetail, $status)
    {
        $log = ArrayToolkit::parts($jobDetail, array(
            'name',
            'pool',
            'source',
            'class',
            'data',
            'priority',
            'status',
        ));

        $log['status'] = $status;
        $log['jobDetailId'] = $jobDetail['id'];

        $this->biz->service('Scheduler:JobLogService')->create($log);
    }

    protected function createJobInstance($fireJob)
    {
        $jobDetail = $fireJob['jobDetail'];
        $class = $fireJob['jobDetail']['class'];
        return new $class($jobDetail);
    }

    protected function getCheckerChain()
    {
        return $this->biz['scheduler.job.checker_chain'];
    }

    protected function getFiredJobDao()
    {
        return $this->biz->dao('Scheduler:FiredJobDao');
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