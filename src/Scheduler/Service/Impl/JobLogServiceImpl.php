<?php

namespace Codeages\Biz\Framework\Scheduler\Service\Impl;

use Codeages\Biz\Framework\Scheduler\Service\JobLogService;
use Codeages\Biz\Framework\Service\BaseService;

class JobLogServiceImpl extends BaseService implements JobLogService
{
    public function create($log)
    {
        // TODO: Implement create() method.
    }

    public function search($condition, $orderBy, $start, $limit)
    {
        // TODO: Implement search() method.
    }

    public function count($condition)
    {
        // TODO: Implement count() method.
    }

    protected function getJobLogDao()
    {
        return $this->biz->dao('Scheduler:');
    }
}