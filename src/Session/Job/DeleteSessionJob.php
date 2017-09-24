<?php

namespace Codeages\Biz\Framework\Session\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class DeleteSessionJob extends AbstractJob
{
    public function execute()
    {
        $this->getSessionService()->gc();
        $this->getOnlineService()->gc();
    }

    protected function getSessionService()
    {
        return $this->biz->service('Session:SessionService');
    }

    protected function getOnlineService()
    {
        return $this->biz->service('Session:OnlineService');
    }
}
