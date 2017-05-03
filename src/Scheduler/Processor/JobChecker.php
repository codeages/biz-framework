<?php

namespace Codeages\Biz\Framework\Scheduler\Processor;

interface JobChecker
{
    public function check($jobDetail);
}