<?php
namespace Codeages\Biz\Framework\Queue;

class Worker
{
    protected $queue;

    protected $options;

    protected $shouldQuit = false;

    public function __construct($queue, array $options = array())
    {
        $this->queue = $queue;
        $this->options = array_merge(array(
            'job_timeout' => 60,
            'memory_limit' => 256,
            'sleep' => 1,
            'tries' => 0,
        ), $options);
    }

    public function run()
    {
        while(true) {
            $job = $this->getNextJob();
            if ($job) {
                $timeout = $job->getMetadata('timeout', $this->options['job_timeout']);
                $this->executeJob($job);
            } else {
                sleep($this->options['sleep']);
            }

            $this->stopIfNecessary();
        }
    }

    protected function getNextJob()
    {
        try {
            return $job = $this->queue->pop();
        } catch(\Exception $e) {
            $this->shouldQuit = true;
        } catch(\Throwable $e) {
            $this->shouldQuit = true;
        }
    }

    protected function executeJob($job)
    {
        try {
            $result = $job->execute();
            if (is_array($result)) {
                $result = array_values($result);
                $code = isset($result[0]) ? $result[0] : null;
                $message = isset($result[1]) ? $result[1] : '';
            } else {
                $code = $result;
                $message = '';
            }
            
            if (empty($code) || $code === Job::FINISHED) {
                $this->queue->delete($job);
                return ;
            }

            if ($code == Job::FAILED_RETRY) {
                $executions = $job->getMetadata('executions', 1);
                if ($executions -1 < $this->options['tries']) {
                    $this->queue->release($job);
                    return ;
                }
            }

            $this->failer->log();
            
        } catch(\Exception $e) {
            $this->shouldQuit = true;
        } catch(\Throwable $e) {
            $this->shouldQuit = true;
        }
    }

    protected function stopIfNecessary()
    {
        if ($this->shouldQuit) {
            exit();
        }

        if ($this->isMemoryExceeded($this->options['memory_limit'])) {
            exit();
        }
    }

    protected function isMemoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    protected function getJobTimeout($job, $options)
    {

    }

    protected function getQueueService()
    {

    }
}