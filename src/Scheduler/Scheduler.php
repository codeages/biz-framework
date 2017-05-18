<?php

namespace Codeages\Biz\Framework\Scheduler;

use Codeages\Biz\Framework\Scheduler\Service\Impl\SchedulerServiceImpl;

class Scheduler
{
    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function schedule($job)
    {
        return $this->getSchedulerService()->schedule($job);
    }

    public function execute()
    {
        $this->getSchedulerService()->execute();
    }

    /**
     * @return SchedulerServiceImpl
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}