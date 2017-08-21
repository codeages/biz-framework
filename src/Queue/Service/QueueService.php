<?php
namespace Codeages\Biz\Framework\Queue\Service;
use Codeages\Biz\Framework\Queue\Job;

interface QueueService
{
    public function pushJob(Job $job, $queue = 'default');
    
    public function releaseJob();

    public function popJob(Job $job, $queue = 'default'); 
}