<?php

namespace Codeages\Biz\Framework\Queue;

use Codeages\Beanstalk\Client;

class BeanstalkQueue implements QueueInterface
{
    protected $client;

    const DEFAULT_PRI = 1000;
    const DEFAULT_DELAY = 0;
    const DEFAULT_TTR = 60;

    public function __construct($options = array())
    {
        $queue = new Client($options);
        $queue->connect();
        $this->queue = $queue;
    }

    public function push($queue, array $body, array $options = array())
    {
        $options = $this->fillOptions($options);
        $this->queue->watch($queue);
        return $this->queue->put($options['pri'], self::DEFAULT_DELAY, $options['ttr'], json_encode($body));
    }

    public function pushDelay($queue, array $body, $delay, array $options = array())
    {
        $options = $this->fillOptions($options);
        $this->queue->watch($queue);
        $this->queue->put($options['pri'], $delay, $options['ttr'], json_encode($body));
    }

    public function pop($queue = null, $timeout = 0)
    {

    }

    protected function fillOptions($options)
    {
        return array_merge(array('pri' => self::DEFAULT_PRI, 'ttr' => self::DEFAULT_TTR), $options);
    }
}