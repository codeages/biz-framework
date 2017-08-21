<?php

namespace Codeages\Biz\Framework\Queue;

abstract class AbstractJob implements Job
{
    protected $container;

    protected $body;

    protected $queue;

    protected $connectionName = 'default';

    public function __construct($body, $queue = 'default', $connectionName = 'default', $container = null)
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

    public function setConnection($connectionName)
    {
        $this->connectionName = $connectionName;
    }

    public function getConnection()
    {
        return $this->connectionName;
    }
}