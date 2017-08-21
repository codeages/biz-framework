<?php
namespace Codeages\Biz\Framework\Queue\Driver;
use Codeages\Biz\Framework\Queue\Job;
use Codeages\Biz\Framework\Queue\Dao\JobDao;
use Codeages\Biz\Framework\Context\Biz;

class DatabaseQueue implements Queue
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function push(Job $job)
    {
        $jobRow = $this->biz->dao('Queue:JobDao')->create(array(
            'queue' => $job->getQueue(),
            'body' => $job->getBody(),
            'available_time' => time(),
        ));

        return $job;
    }

    public function release()
    {
    }

    public function pop($queue = null)
    {
    }
}
