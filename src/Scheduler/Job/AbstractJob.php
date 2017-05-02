<?php

namespace Codeages\Biz\Framework\Scheduler\Job;

class AbstractJob implements Job
{
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function execute()
    {

    }
}