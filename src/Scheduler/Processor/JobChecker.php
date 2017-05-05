<?php

namespace Codeages\Biz\Framework\Scheduler\Processor;

interface JobChecker
{
    const EXECUTING = 'executing';

    public function check($jobDetail);
}