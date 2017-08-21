<?php
namespace Codeages\Biz\Framework\Queue;

interface Job
{
    public function execute();

    public function getQueue();

    public function getConnectionName();
}
