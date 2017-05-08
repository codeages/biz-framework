<?php

namespace Codeages\Biz\Framework\Scheduler\Checker;

interface JobChecker
{
    const EXECUTING = 'executing';

    public function check($jobFired);
}