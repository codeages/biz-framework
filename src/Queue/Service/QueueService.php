<?php
namespace Codeages\Biz\Framework\Queue\Service;
use Codeages\Biz\Framework\Queue\Job;

interface QueueService
{
    public function pushJob(Job $job);
    
    public function releaseJob();

    public function popJob($queue = null); 
}