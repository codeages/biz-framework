<?php

namespace Codeages\Biz\Framework\Queue\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Queue\Job;

class QueueServiceImpl extends BaseService implements QueueService
{
    public function pushJob(Job $job, $queue = 'default')
    {
        $queue = $this->biz['queue.connection.'.$job->getConnection()];
        return $queue->push($job, $queue);
    }
    
    public function releaseJob()
    {

    }

    public function popJob(Job $job, $queue = 'default')
    {
        
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Queue:JobDao');
    }
}
