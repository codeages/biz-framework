<?php

namespace Codeages\Biz\Framework\Queue\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Queue\Job;

class QueueServiceImpl extends BaseService implements QueueService
{
    public function pushJob(Job $job)
    {
        $queue = $this->biz['queue.connection.'.$job->getConnectionName()];
        return $queue->push($job, $queue);
    }
    
    public function releaseJob()
    {

    }

    public function popJob($queue = null)
    {
        
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Queue:JobDao');
    }
}
