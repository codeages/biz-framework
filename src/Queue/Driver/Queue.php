<?php
namespace Codeages\Biz\Framework\Queue\Driver;
use Codeages\Biz\Framework\Queue\Job;

interface Queue
{
    public function push(Job $job, $queue);

    public function release();

    public function pop(Job $job, $queue); 
}
