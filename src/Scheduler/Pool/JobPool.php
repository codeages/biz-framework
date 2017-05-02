<?php

namespace Codeages\Biz\Framework\Scheduler\Pool;

use Codeages\Biz\Framework\Scheduler\Job\Job;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class JobPool
{
    private $data = array();
    private $biz;

    public function __construct($options)
    {
        $this->data = $options;
    }

    public function execute(Job $job)
    {
        $this->data['group'] = $job['group'];
        $jobPool = $this->initPool($this->data);

        if ($jobPool['num'] == $jobPool['maxNum']) {
            throw new AccessDeniedException('job pool is full.');
        }

        $this->wavePoolNum($jobPool['id'], 1);

        $this->runJob($job);

        $this->wavePoolNum($jobPool['id'], -1);
    }

    protected function runJob(Job $job)
    {
        $job->execute();
    }

    protected function initPool($options)
    {
        $jobPool = $this->getJobPoolDao()->getJobPoolByGroup($job['group']);
        if (empty($jobPool)) {
            $jobPool = ArrayToolkit::parts($options, array('group', 'maxNum', 'num', 'timeOut'));
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

    protected function getBiz()
    {
        return $this->biz;
    }

    protected function getJobPoolDao()
    {
        $this->getBiz()->dao('Scheduler:JobPoolDao');
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