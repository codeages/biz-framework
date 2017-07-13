<?php

namespace Codeages\Biz\Framework\Security\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class SessionTimeoutJob extends AbstractJob
{
    public function execute()
    {
        $sessTime = time() - $this->biz['session.manager.timeout'];
        $this->getSessionManager()->deleteInvalidSessions($sessTime);
    }

    protected function getSessionManager()
    {
        return $this->biz->service('Security:SessionManager');
    }
}