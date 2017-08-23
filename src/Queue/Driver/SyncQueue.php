<?php
namespace Codeages\Biz\Framework\Queue\Driver;
use Pimple\Container;
use Codeages\Biz\Framework\Queue\Job;

class SyncQueue extends AbstractQueue implements Queue
{
    protected $jobs = array();

    public function push(Job $job, array $options = array())
    {
        if (!empty($this->options['async_execute'])) {
            $this->jobs[] = $job;
            return ;
        }
        
        $job->setId(uniqid());
        $job->setBiz($this->biz);
        $job->execute();
    }

    public function pop(array $options = array())
    {
        $job = array_shift($this->jobs);
        if (empty($job)) {
            return null;
        }
        $job->setBiz($this->biz);

        return $job;
    }

    public function delete(Job $job)
    {

    }

    public function release(Job $job, array $options = array())
    {

    }

    public function bury(Job $job, array $options = array())
    {

    }

    public function peek($id)
    {

    }
}
