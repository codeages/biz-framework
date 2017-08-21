<?php
namespace Codeages\Biz\Framework\Queue\Driver;
use Codeages\Biz\Framework\Queue\Job;

interface Queue
{
    public function push(Job $job);

    public function release();

    public function pop($queue = null); 
}
