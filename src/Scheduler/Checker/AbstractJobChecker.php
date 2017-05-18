<?php

namespace Codeages\Biz\Framework\Scheduler\Checker;

abstract class AbstractJobChecker
{
    const EXECUTING = 'executing';

    protected $biz;

    function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function check($jobFired);
}