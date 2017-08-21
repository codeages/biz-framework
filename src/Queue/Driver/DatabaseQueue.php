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
            'expired_time' => time() + $job->getTimeout(),
        ));

        return $job;
    }

    public function release()
    {
    }

    public function pop($queue = null)
    {
        $queue = $queue ? $queue : 'default';

        $this->biz['db']->beginTransaction();
        $jobRow = $this->getJobDao()->getNextJob($queue);
        if ($jobRow) {
            $this->getJobDao()->update($jobRow['id'], array(
                'reserved_time' => time(),
                'attempts' => $jobRow['attempts'] + 1,
            ));
        }

        $this->biz['db']->commit();

        $job = new $jobRow['class']($jobRow['body'], $jobRow['queue']);

        return $job;
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Queue:JobDao');
    }
}
