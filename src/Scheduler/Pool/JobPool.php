<?php

namespace Codeages\Biz\Framework\Scheduler\Pool;

use Codeages\Biz\Framework\Scheduler\Job\Job;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class JobPool
{
    private $options = array();
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
        $this->options = $biz['scheduler.job.pool.options'];
    }

    public function execute(Job $job)
    {
        $this->options['group'] = $job['group'];
        $jobPool = $this->initPool($this->options);

        if ($jobPool['num'] == $jobPool['maxNum']) {
            throw new AccessDeniedException('job pool is full.');
        }

        $this->wavePoolNum($jobPool['id'], 1);

        try {
            $this->runJob($job);
        } catch (\Exception $e) {
            $this->wavePoolNum($jobPool['id'], -1);
            throw new \RuntimeException($e->getMessage());
        }

        $this->wavePoolNum($jobPool['id'], -1);
    }

    public function getPoolDetail($name = 'default')
    {
        return $this->getJobPoolDao()->getByName($name);
    }

    protected function runJob(Job $job)
    {
        $job->execute();
    }

    protected function initPool($options)
    {
        $jobPool = $this->getJobPoolDao()->getByName($options['group']);
        if (empty($jobPool)) {
            $jobPool = ArrayToolkit::parts($options, array('maxNum', 'num', 'timeout'));
            $jobPool['name'] = $options['group'];

            $jobPool = $this->getJobPoolDao()->create($jobPool);
        }
        return $jobPool;
    }

    protected function wavePoolNum($id, $diff)
    {
        $ids = array($id);
        $diff = array('num' => $diff);
        $this->getJobPoolDao()->wave($ids, $diff);
    }

    protected function getJobPoolDao()
    {
        return $this->biz->dao('Scheduler:JobPoolDao');
    }

    public function __get($name)
    {
        return empty($this->data[$name] ) ? '' : $this->data[$name];
    }

    function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
}