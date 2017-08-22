<?php
namespace Codeages\Biz\Framework\Queue;

class Worker
{
    protected $queue;

    protected $options;

    public function __construct($queue, array $options = array())
    {
        $this->queue = $queue;
        $this->options = array_merge(array(
            'job_timeout' => 60,
        ), $options);
    }

    public function run()
    {
        while(true) {
            $job = $this->queue->pop();
            $job->execute();
            $this->queue->delete($job);
        }
    }

    protected function getJobTimeout($job, $options)
    {

    }

    protected function getQueueService()
    {

    }
}