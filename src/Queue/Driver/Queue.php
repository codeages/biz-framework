<?php
namespace Codeages\Biz\Framework\Queue\Driver;
use Codeages\Biz\Framework\Queue\Job;

interface Queue
{
    public function push(Job $job, array $options = array());

    public function pop(array $options = array()); 

    public function delete(Job $job);

    public function release(Job $job, array $options = array());

    public function bury(Job $job, array $options = array());
}
