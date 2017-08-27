<?php

namespace Codeages\Biz\Framework\Queue;

use Codeages\Biz\Framework\Queue\Driver\Queue;
use Symfony\Component\Lock\LockInterface;

class Worker
{
    const EXIT_CODE_MEMORY_EXCEEDED = 1;

    const EXIT_CODE_TIMEOUT = 2;

    const EXIT_CODE_EXCEPTION = 3;

    const EXIT_CODE_RUNNING = 4;

    protected $queue;

    protected $options;

    protected $shouldQuit = false;

    protected $failer;

    protected $lock;

    public function __construct(Queue $queue, JobFailer $failer, LockInterface $lock, array $options = array())
    {
        $this->queue = $queue;
        $this->failer = $failer;
        $this->lock = $lock;
        $this->options = array_merge(array(
            'memory_limit' => 256,
            'sleep' => 2,
            'tries' => 0,
            'once' => false,
            'stop_when_idle' => false,
        ), $options);
    }

    public function run()
    {
        $acquired = $this->lock->acquire();
        if (!$acquired) {
            $this->stop(self::EXIT_CODE_RUNNING);
        }

        while (true) {
            $job = $this->runNextJob();
            if (empty($job)) {
                sleep($this->options['sleep']);
            }
            $this->stopIfNecessary($job);
        }

        $this->lock->release();
    }

    public function runNextJob()
    {
        $job = $this->getNextJob();
        if ($job) {
            $this->executeJob($job);

            return $job;
        }
    }

    protected function getNextJob()
    {
        try {
            return $job = $this->queue->pop();
        } catch (\Exception $e) {
            $this->shouldQuit = true;
        } catch (\Throwable $e) {
            $this->shouldQuit = true;
        }
    }

    protected function executeJob($job)
    {
        $this->registerTimeoutHandler($job);

        try {
            $result = $job->execute();
        } catch (\Exception $e) {
            $this->shouldQuit = true;
        } catch (\Throwable $e) {
            $this->shouldQuit = true;
        }

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

            return;
        }

        if ($code == Job::FAILED_RETRY) {
            $executions = $job->getMetadata('executions', 1);
            if ($executions - 1 < $this->options['tries']) {
                $this->queue->release($job);

                return;
            }
        }

        $this->failer->log($job, $this->queue->getName(), $message);
        $this->queue->delete($job);
    }

    protected function registerTimeoutHandler($job)
    {
        $timeout = $job->getMetadata('timeout', 0);
        if (empty($timeout)) {
            return;
        }

        if ($this->isSupportAsyncSignal()) {
            pcntl_async_signals(true);
            pcntl_signal(SIGALRM, function () {
                $this->kill(self::EXIT_CODE_TIMEOUT);
            });

            pcntl_alarm($timeout);
        }
    }

    protected function isSupportAsyncSignal()
    {
        return version_compare(PHP_VERSION, '7.1.0') >= 0 &&
               extension_loaded('pcntl');
    }

    public function kill($status = 0)
    {
        if (extension_loaded('posix')) {
            posix_kill(getmypid(), SIGKILL);
        }

        $this->stop($status);
    }

    protected function stop($status = 0)
    {
        $this->lock->release();
        exit($status);
    }

    protected function stopIfNecessary($job)
    {
        if ($this->shouldQuit) {
            $this->stop(self::EXIT_CODE_EXCEPTION);
        }

        if (empty($job) && $this->options['stop_when_idle']) {
            $this->stop();
        }

        if ($this->options['once'] == true) {
            $this->stop();
        }

        if ($this->isMemoryExceeded($this->options['memory_limit'])) {
            $this->stop(self::EXIT_CODE_MEMORY_EXCEEDED);
        }
    }

    protected function isMemoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }
}
