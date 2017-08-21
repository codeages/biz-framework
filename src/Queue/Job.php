<?php
namespace Codeages\Biz\Framework\Queue;

interface Job
{
    public function execute();

    public function getTimeout();

    public function getQueue();

    public function getConnectionName();
}
