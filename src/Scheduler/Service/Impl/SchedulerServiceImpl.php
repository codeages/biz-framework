<?php

namespace Codeages\Biz\Framework\Scheduler\Service\Impl;

use Codeages\Biz\Framework\Scheduler\Service\JobPool;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Util\Lock;
use Cron\CronExpression;

class SchedulerServiceImpl extends BaseService implements SchedulerService
{
    const EXECUTING = 'executing';

    public function register($job)
    {
        if (empty($job['expression'])) {
            throw new InvalidArgumentException('expression is empty.');
        }

        if (empty($job['name'])) {
            throw new InvalidArgumentException('name is empty.');
        }

        if (empty($job['class'])) {
            throw new InvalidArgumentException('class is empty.');
        }

        if (is_integer($job['expression'])) {
            $job['next_fire_time'] = $job['expression'] - $job['expression'] % 60;
            unset($job['expression']);
        } else {
            if (!CronExpression::isValidExpression($job['expression'])) {
                throw new InvalidArgumentException('expression is invalid.');
            }

            $job['next_fire_time'] = $this->getNextFireTime($job['expression']);
        }

        $default = array(
            'misfire_threshold' => 300,
            'misfire_policy' => 'missed',
            'priority' => 100,
            'source' => 'MAIN',
        );

        $job = array_merge($default, $job);

        $job = $this->getJobDao()->create($job);
        $this->dispatch('scheduler.job.created', $job);

        $jobFired['job'] = $job;

        $this->createJobLog($jobFired, 'created');

        return $job;
    }

    protected function getExpression($time)
    {
        $year = date('Y', $time);
        $month = date('m', $time);
        $day = date('d', $time);
        $hour = date('G', $time);
        $min = date('i', $time);

        return "{$min} {$hour} {$day} {$month} * {$year}";
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

    public function findJobFiredsByJobId($jobId)
    {
        return $this->getJobFiredDao()->findByJobId($jobId);
    }

    public function deleteJob($id)
    {
        $job = $this->getJobDao()->update($id, array(
            'deleted' => 1,
            'deleted_time' => time(),
        ));

        $this->createJobLog(array('job' => $job), 'delete');
    }

    public function deleteJobByName($name)
    {
        $job = $this->getJobDao()->getByName($name);
        if (!empty($job)) {
            $this->deleteJob($job['id']);
        }
    }

    protected function check($jobFired)
    {
        $result = $this->checkExecuting($jobFired);
        if (static::EXECUTING != $result) {
            return $result;
        }

        return $this->checkMisfire($jobFired);
    }

    protected function checkExecuting($jobFired)
    {
        $jobFireds = $this->findJobFiredsByJobId($jobFired['job_id']);
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

    protected function checkMisfire($jobFired)
    {
        $now = time();
        $job = $jobFired['job'];
        $fireTime = $job['next_fire_time'];

        if (!empty($job['misfire_threshold']) && ($now - $fireTime) > $job['misfire_threshold']) {
            return $job['misfire_policy'];
        }

        return static::EXECUTING;
    }

    protected function jobExecuted($jobFired, $result)
    {
        if ($result == 'success') {
            $this->getJobFiredDao()->update($jobFired['id'], array(
                'status' => 'success',
            ));
            $this->createJobLog($jobFired, 'success');
        } elseif ($result == 'retry') {
            $this->getJobFiredDao()->update($jobFired['id'], array(
                'fired_time' => time(),
                'status' => 'acquired',
            ));
            $this->createJobLog($jobFired, 'acquired');
        } else {
            $this->getJobFiredDao()->update($jobFired['id'], array(
                'fired_time' => time(),
                'status' => $result,
            ));
            $this->createJobLog($jobFired, $result);
        }

        $this->dispatch('scheduler.job.executed', $jobFired, array('result' => $result));
    }

    protected function getNextFireTime($expression)
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

        $job = $this->getJobDao()->get($createdJobFired['job_id']);
        $createdJobFired['job'] = $job;
        $result = $this->check($createdJobFired);

        $jobFired = $this->getJobFiredDao()->update($createdJobFired['id'], array('status' => $result));

        $jobFired['job'] = $job;

        $this->createJobLog($jobFired, $result);

        if ($result == self::EXECUTING) {
            $this->dispatch('scheduler.job.executing', $jobFired);

            return $jobFired;
        }

        return $this->getAcquiredJob();
    }

    protected function updateNextFireTime($job)
    {
        if ($job['next_fire_time'] > time()) {
            return $job;
        }

        if (empty($job['expression'])) {
            $this->deleteJob($job['id']);

            return $job;
        }

        $nextFireTime = $this->getNextFireTime($job['expression']);

        $fields = array(
            'pre_fire_time' => $job['next_fire_time'],
            'next_fire_time' => $nextFireTime,
        );

        return $this->getJobDao()->update($job['id'], $fields);
    }

    protected function updateWaitingJobsToAcquired()
    {
        $lock = new Lock($this->biz);
        $lockName = 'scheduler.job.acquire_jobs';

        try {
            $lock->get($lockName, 20);
            $this->biz['db']->beginTransaction();

            $jobs = $this->getJobDao()->findWaitingJobsByLessThanFireTime(time());

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
        $jobFired = array(
            'job_id' => $job['id'],
            'fired_time' => $job['next_fire_time'],
            'status' => 'acquired',
        );
        $jobFired = $this->getJobFiredDao()->create($jobFired);
        $jobFired['job'] = $this->updateNextFireTime($job);

        $this->dispatch('scheduler.job.acquired', $jobFired);

        $this->createJobLog($jobFired, 'acquired');
    }

    protected function createJobLog($jobFired, $status)
    {
        $job = $jobFired['job'];
        $log = ArrayToolkit::parts($job, array(
            'name',
            'source',
            'class',
            'args',
            'priority',
            'status',
        ));

        if (!empty($jobFired['id'])) {
            $log['job_fired_id'] = $jobFired['id'];
        }
        $log['status'] = $status;
        $log['job_id'] = $job['id'];
        $log['hostname'] = gethostname();

        $this->getJobLogDao()->create($log);
    }

    protected function createJobEnabledLog($job, $enableStatus)
    {
        $log = ArrayToolkit::parts($job, array(
            'name',
            'source',
            'class',
            'args',
            'priority',
        ));

        $log['status'] = $enableStatus;
        $log['job_id'] = $job['id'];
        $log['hostname'] = gethostname();

        $this->getJobLogDao()->create($log);
    }

    public function searchJobLogs($condition, $orderBy, $start, $limit)
    {
        return $this->getJobLogDao()->search($condition, $orderBy, $start, $limit);
    }

    public function countJobLogs($condition)
    {
        return $this->getJobLogDao()->count($condition);
    }

    public function searchJobs($condition, $orderBy, $start, $limit)
    {
        $condition = $this->mergeCondition($condition);

        return $this->getJobDao()->search($condition, $orderBy, $start, $limit);
    }

    public function countJobs($condition)
    {
        $condition = $this->mergeCondition($condition);

        return $this->getJobDao()->count($condition);
    }

    public function searchJobFires($condition, $orderBy, $start, $limit)
    {
        return $this->getJobFiredDao()->search($condition, $orderBy, $start, $limit);
    }

    public function countJobFires($condition)
    {
        return $this->getJobFiredDao()->count($condition);
    }

    public function enabledJob($jobId)
    {
        $job = $this->getJobDao()->update($jobId, array('enabled' => 1));
        $this->createJobEnabledLog($job, 'enabled');

        return $job;
    }

    public function disabledJob($jobId)
    {
        $job = $this->getJobDao()->update($jobId, array('enabled' => 0));
        $this->createJobEnabledLog($job, 'disabled');

        return $job;
    }

    protected function mergeCondition($condition)
    {
        $defaultCondition = array(
            'deleted' => 0,
        );

        return array_merge($defaultCondition, $condition);
    }

    protected function createJobInstance($jobFired)
    {
        $job = $jobFired['job'];
        $class = $jobFired['job']['class'];

        return new $class($job, $this->biz);
    }

    protected function getJobFiredDao()
    {
        return $this->biz->dao('Scheduler:JobFiredDao');
    }

    protected function getJobLogDao()
    {
        return $this->biz->dao('Scheduler:JobLogDao');
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }

    protected function getJobPool()
    {
        return new JobPool($this->biz);
    }
}
