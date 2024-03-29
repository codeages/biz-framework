<?php

namespace Codeages\Biz\Framework\Queue\Driver;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Queue\Job;
use Codeages\Biz\Framework\Queue\QueueException;
use Doctrine\DBAL\Types\Types;

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

    public function push(Job $job)
    {
        try {
            $jobRecord = array(
                'queue' => $this->name,
                'body' => serialize($job->getBody()),
                'class' => get_class($job),
                'timeout' => $job->getMetadata('timeout', Job::DEFAULT_TIMEOUT),
                'priority' => $job->getMetadata('priority', Job::DEFAULT_PRIORITY),
                'available_time' => time() + $job->getMetadata('delay', 0),
            );

            $this->biz['db']->insert($this->options['table'], $jobRecord, array(
                Types::STRING,
                Types::TEXT,
                Types::STRING,
                Types::INTEGER,
                Types::INTEGER,
                Types::INTEGER,
            ));
            $id = $this->biz['db']->lastInsertId();
            $job->setId($id);
            $job->setBiz($this->biz);
        } catch (\Exception $e) {
            throw new QueueException('Push job failed', 0, $e);
        }
    }

    public function pop(array $options = array())
    {
        $this->biz['db']->beginTransaction();

        $sql = "SELECT * FROM {$this->options['table']} WHERE queue = ? AND (reserved_time = 0 AND available_time <= ?) OR (reserved_time > 0 AND expired_time <= ?) ORDER BY id ASC FOR UPDATE;";
        $now = time();
        $record = $this->biz['db']->fetchAssoc($sql, array(
            $this->name,
            $now,
            $now,
        ), array(
            Types::STRING,
            Types::INTEGER,
            Types::INTEGER,
        ));
        if (empty($record)) {
            $this->biz['db']->commit();

            return null;
        }

        $this->biz['db']->update($this->options['table'], array(
            'reserved_time' => time(),
            'executions' => $record['executions'] + 1,
            'expired_time' => time() + $record['timeout'],
        ), array(
            'id' => $record['id'],
        ), array(
            Types::INTEGER,
            Types::INTEGER,
            Types::INTEGER,
        ));

        $this->biz['db']->commit();

        try {
            $class = $record['class'];
            $job = new $class();
            $job->setId($record['id']);
            $job->setBody(unserialize($record['body']));
            $job->setMetadata(array(
                'class' => $class,
                'timeout' => $record['timeout'],
                'priority' => $record['priority'],
                'executions' => $record['executions'] + 1,
            ));
            $job->setBiz($this->biz);
        } catch (\Throwable $e) {
            $this->biz['db']->delete($this->options['table'], array('id' => $record['id'],), array(Types::INTEGER,));
            throw new QueueException('Pop job failed', 0, $e);
        }

        return $job;
    }

    public function delete(Job $job)
    {
        $this->biz['db']->delete($this->options['table'], array(
            'id' => $job->getId(),
        ), array(
            Types::INTEGER,
        ));
    }

    public function release(Job $job)
    {
        $this->biz['db']->update($this->options['table'], array(
            'reserved_time' => 0,
            'expired_time' => 0,
        ), array(
            'id' => $job->getId(),
        ), array(
            Types::INTEGER,
            Types::INTEGER,
            Types::INTEGER,
        ));
    }
}
