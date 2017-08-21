<?php
namespace Codeages\Biz\Framework\Queue\Driver;
use Pimple\Container;
use Codeages\Biz\Framework\Queue\Job;

class SyncQueue implements Queue
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function push(Job $job, $queue)
    {
        $job->setContainer($this->container);
        $job->execute();
        return $job;
    }

    public function release()
    {
    }

    public function pop(Job $job, $queue)
    {
    }
}
