<?php
namespace Codeages\Biz\Framework\Queue\Driver;
use Codeages\Biz\Framework\Queue\Job;
use Codeages\Biz\Framework\Queue\Dao\JobDao;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Queue\QueueException;
use Doctrine\DBAL\Types\Type;

class DatabaseQueue extends AbstractQueue implements Queue
{
    protected $table;

    public function __construct($name, Biz $biz, array $options = array())
    {
        $options = array_merge(array(
            'table' => 'biz_queue_job',
        ), $options);

        parent::__construct($name, $biz, $options);
    }

    public function push(Job $job, array $options = array())
    {
        $options = $this->mergeJobOptions($options);

        try {
            $this->biz['db']->insert($this->options['table'], array(
                'queue' => $this->name,
                'class' => get_class($job),
                'body' => serialize($job->getBody()),
                'available_time' => time(),
                'expired_time' => time() + $options['timeout']
            ), array(
                Type::STRING,
                Type::STRING,
                Type::TEXT,
                Type::INTEGER,
                Type::INTEGER,
            ));
            $id = $this->biz['db']->lastInsertId();
            $job->setId($id);
        } catch (\Exception $e) {
            throw new QueueException("Push job failed", 0, $e);
        }
    }

    public function pop(array $options = array())
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

    public function delete(Job $job)
    {

    }
    
    public function release(Job $job, array $options = array())
    {

    }

    public function bury(Job $job, array $options = array())
    {

    }
}
