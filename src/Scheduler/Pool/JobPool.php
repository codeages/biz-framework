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

        $jobPool = $this->prepare($this->options);

        try {
            $job->execute();
        } catch (\Exception $e) {
            $this->release($jobPool['id']);
            throw new \RuntimeException($e->getMessage());
        }

        $this->release($jobPool);
    }

    public function getPoolDetail($name = 'default')
    {
        return $this->getJobPoolDao()->getByName($name);
    }

    protected function release($jobPool)
    {
        $lockName = "job_pool.{$jobPool['name']}";
        $this->biz['lock']->get($lockName, 10);

        $this->wavePoolNum($jobPool['id'], -1);

        $this->biz['lock']->release($lockName);
    }

    protected function prepare($options)
    {
        $lockName = "job_pool.{$options['group']}";
        $this->biz['lock']->get($lockName, 10);


        $jobPool = $this->getPoolDetail($options['group']);
        if (empty($jobPool)) {
            $jobPool = ArrayToolkit::parts($options, array('maxNum', 'num', 'timeout'));
            $jobPool['name'] = $options['group'];

            $jobPool = $this->getJobPoolDao()->create($jobPool);
        }

        if ($jobPool['num'] == $jobPool['maxNum']) {

            $this->biz['lock']->release($lockName);
            throw new AccessDeniedException('job pool is full.');
        }

        $this->wavePoolNum($jobPool['id'], 1);

        $this->biz['lock']->release($lockName);
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