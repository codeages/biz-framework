<?php

namespace Codeages\Biz\Framework\Scheduler\Processor;

class CheckerChain
{
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function check($jobDetail)
    {
        $processors = $this->getProcessors();
        foreach ($processors as $processor) {
            $result = $processor->check($jobDetail);
            if ($result != JobChecker::EXECUTING) {
                return $result;
            }
        }

        return JobChecker::EXECUTING;
    }

    protected function getProcessors()
    {
        return $this->biz['scheduler.job.processors'];
    }
}