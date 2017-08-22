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
                'payload' => serialize(array(
                    'body' => $job->getBody(),
                    'metadata' => array(
                        'class' => get_class($job),
                        'timeout' => $options['timeout'],
                    ),
                )),
                'available_time' => time(),
                'expired_time' => time() + $options['timeout']
            ), array(
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
        $this->biz['db']->beginTransaction();

        $sql = "SELECT * FROM {$this->options['table']} WHERE queue = ? AND (reserved_time = 0 AND available_time <= ?) OR (reserved_time > 0 AND expired_time <= ?) ORDER BY id ASC FOR UPDATE;";
        $now = time();
        $record = $this->biz['db']->fetchAssoc($sql, array($this->name, $now, $now)) ?: null;
        if (empty($record)) {
            $this->biz['db']->commit();
            return null;
        }

        $this->biz['db']->update($this->options['table'], array(
            'reserved_time' => time(),
            'attempts' => $record['attempts'] + 1,
        ), array(
            'id' => $record['id'],
        ), array(
            Type::INTEGER,
            Type::INTEGER,
        ));

        $this->biz['db']->commit();

        $payload = unserialize($record['payload']);
        $class = $payload['metadata']['class'];

        $job = new $class();
        $job->setId($record['id']);
        $job->setBody($payload['body']);
        $job->setMetadata($payload['metadata']);
        $job->setBiz($this->biz);

        return $job;
    }

    public function delete(Job $job)
    {
        $this->biz['db']->delete($this->options['table'], array(
            'id' => $job->getId(),
        ), array(
            Type::INTEGER,
        ));
    }
    
    public function release(Job $job, array $options = array())
    {

    }

    public function bury(Job $job, array $options = array())
    {

    }
}
