<?php

namespace Codeages\Biz\Framework\Scheduler\Service\Impl;

use Codeages\Biz\Framework\Scheduler\Checker\JobChecker;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Util\Lock;
use Cron\CronExpression;

class SchedulerServiceImpl extends BaseService implements SchedulerService
{
    public function schedule($job)
    {
        if (empty($job['expression']) && empty($job['nextFireTime'])) {
            throw new InvalidArgumentException('args is invalid.');
        }

        if (!empty($job['expression']) && !CronExpression::isValidExpression($job['expression'])) {
            throw new InvalidArgumentException('cron expression is invalid.');
        }

        if (!empty($job['expression'])) {
            $job['nextFireTime'] = $this->getNextRunTime($job['expression']);
        }

        $default = array(
            'misfireThreshold' => 300,
            'misfirePolicy' => 'missed',
            'priority' => 100,
            'pool' => 'default',
            'source' => 'MAIN'
        );
        $job = array_merge($default, $job);

        $job = $this->getJobDao()->create($job);
        $this->dispatch('job.created', $job);

        $jobFired['job'] = $job;

        $this->createJobLog($jobFired, 'created');

        return $job;
    }

    public function execute()
    {
        $this->updateWaitingJobsToAcquired();
        $jobFired = $this->triggerJob();
        if (empty($jobFired)) {
            return;
        }

        $jobInstance = $this->createJobInstance($jobFired);
        $result = $this->getJobPool()->execute($jobInstance);

        $this->jobExecuted($jobFired, $result);
    }

    public function deleteJob($id)
    {
        $this->getJobDao()->update($id, array(
            'deleted' => 1,
            'deletedTime' => time()
        ));
    }

    public function clearJobs()
    {
        $jobs = $this->getJobDao()->search(array(
            'deleted' => 1,
            'lessThanDeletedTime' => time() - 24*60*60
        ), array(), 0, 100);

        foreach ($jobs as $job) {
            $this->getJobDao()->delete($job['id']);
        }
    }

    public function deleteJobByPoolAndName($pool, $name)
    {
        $job = $this->getJobDao()->getByPoolAndName($pool, $name);
        $this->deleteJob($job['id']);
    }

    protected function jobExecuted($jobFired, $result)
    {
        if ($result != 'success') {
            $this->createJobLog($jobFired, $result);
            $this->getJobFiredDao()->update($jobFired['id'], array(
                'firedTime' => time(),
                'status' => 'acquired'
            ));
            $this->createJobLog($jobFired, 'acquired');
        } else {
            $this->getJobFiredDao()->update($jobFired['id'], array(
                'status' => 'success'
            ));
            $this->createJobLog($jobFired, 'success');
        }

        $this->dispatch('job.executed', $jobFired, array('result' => $result));
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

            $jobFired = $this->getAcquiredJob();

            $this->biz['db']->commit();
            $lock->release($lockName);

            return $jobFired;
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            $lock->release($lockName);
            throw new ServiceException($e);
        }
    }

    protected function getAcquiredJob()
    {
        $createdJobFired = $this->getJobFiredDao()->getByStatus('acquired');
        if (empty($createdJobFired)) {
            return;
        }

        $job = $this->getJobDao()->get($createdJobFired['jobId']);
        $createdJobFired['job'] = $job;
        $result =  $this->getCheckerChain()->check($createdJobFired);

        $jobFired = $this->getJobFiredDao()->update($createdJobFired['id'], array('status' => $result));

        $jobFired['job'] = $job;
        $this->updateNextFireTime($jobFired);

        $this->createJobLog($jobFired, $result);

        if ($result == JobChecker::EXECUTING) {
            $this->dispatch('job.executing', $jobFired);
            return $jobFired;
        }

        return $this->getAcquiredJob();
    }

    protected function updateNextFireTime($jobFired)
    {
        $job = $jobFired['job'];

        $nextFireTime = $job['nextFireTime'];
        if (!empty($job['expression'])) {
            $nextFireTime = $this->getNextRunTime($job['expression']);
        }

        $fields = array(
            'status' => 'waiting',
            'preFireTime' => $job['nextFireTime'],
            'nextFireTime' => $nextFireTime
        );

        $this->getJobDao()->update($job['id'], $fields);
    }

    protected function updateWaitingJobsToAcquired()
    {
        $lock = new Lock($this->biz);
        $lockName = 'scheduler.job.acquire_jobs';

        try {
            $lock->get($lockName, 20);
            $this->biz['db']->beginTransaction();

            $jobs = $this->getJobDao()->findWaitingJobsByLessThanFireTime(strtotime('+1 minutes'));

            foreach ($jobs as $job) {
                $this->updateJobToAcquired($job);
            }

            $this->biz['db']->commit();
            $lock->release($lockName);


        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            $lock->release($lockName);
            throw new ServiceException($e);
        }
    }

    protected function updateJobToAcquired($job)
    {
        $job = $this->getJobDao()->update($job['id'], array('status' => 'acquired'));

        $jobFired = array(
            'jobId' => $job['id'],
            'firedTime' => $job['nextFireTime'],
            'status' => 'acquired'
        );
        $jobFired = $this->getJobFiredDao()->create($jobFired);
        $jobFired['job'] = $job;

        $this->dispatch('job.acquired', $jobFired);

        $this->createJobLog($jobFired, 'acquired');
    }

    protected function createJobLog($jobFired, $status)
    {
        $job = $jobFired['job'];
        $log = ArrayToolkit::parts($job, array(
            'name',
            'pool',
            'source',
            'class',
            'args',
            'priority',
            'status',
        ));

        if (!empty($jobFired['id'])) {
            $log['jobFiredId'] = $jobFired['id'];
        }
        $log['status'] = $status;
        $log['jobDetailId'] = $job['id'];
        $log['hostname'] = getHostName();

        $this->biz->service('Scheduler:JobLogService')->create($log);
    }

    protected function createJobInstance($jobFired)
    {
        $job = $jobFired['job'];
        $class = $jobFired['job']['class'];
        return new $class($job, $this->biz);
    }

    protected function getCheckerChain()
    {
        return $this->biz['scheduler.job.checker_chain'];
    }

    protected function getJobFiredDao()
    {
        return $this->biz->dao('Scheduler:JobFiredDao');
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