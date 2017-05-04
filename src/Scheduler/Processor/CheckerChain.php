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
        $processors = $this->biz['scheduler.job.processors'];
        foreach ($processors as $processor) {
            $result = $processor->check($jobDetail);
            switch ($result) {
                case '';
            }
            if ($result) {
                return $result;
            }
        }

        return false;
    }
}