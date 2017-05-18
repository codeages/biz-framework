<?php

namespace Codeages\Biz\Framework\Scheduler\Checker;

class CheckerChain
{
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function check($jobFired)
    {
        $checkers = $this->getCheckers();
        foreach ($checkers as $checker) {
            $result = $checker->check($jobFired);
            if ($result != AbstractJobChecker::EXECUTING) {
                return $result;
            }
        }

        return AbstractJobChecker::EXECUTING;
    }

    protected function getCheckers()
    {
        return $this->biz['scheduler.job.checkers'];
    }
}