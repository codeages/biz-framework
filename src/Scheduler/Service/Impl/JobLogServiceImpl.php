<?php

namespace Codeages\Biz\Framework\Scheduler\Service\Impl;

use Codeages\Biz\Framework\Scheduler\Service\JobLogService;
use Codeages\Biz\Framework\Service\BaseService;

class JobLogServiceImpl extends BaseService implements JobLogService
{
    public function create($log)
    {
        return $this->getJobLogDao()->create($log);
    }

    public function search($condition, $orderBy, $start, $limit)
    {
        return $this->getJobLogDao()->search($condition, $orderBy, $start, $limit);
    }

    public function count($condition)
    {
        return $this->getJobLogDao()->count($condition);
    }

    protected function getJobLogDao()
    {
        return $this->biz->dao('Scheduler:JobLogDao');
    }
}