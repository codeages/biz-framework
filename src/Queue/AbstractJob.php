<?php

namespace Codeages\Biz\Framework\Queue;

abstract class AbstractJob implements Job
{
    protected $container;

    protected $body;

    protected $queue;

    protected $connectionName;

    public function __construct($body, $queue = null, $connectionName = null, $container = null)
    {
        $this->body = $body;
        $this->queue = $queue;
        $this->connectionName = $connectionName;
        $this->container = $container;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getQueue()
    {
        return $this->queue ? $this->queue : 'default';
    } 

    public function getConnectionName()
    {
        return $this->connectionName ? $this->connectionName : 'default';
    }
}