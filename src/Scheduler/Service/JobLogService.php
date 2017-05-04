<?php

namespace Codeages\Biz\Framework\Scheduler\Service;

interface JobLogService
{
    public function create($log);

    public function search($condition, $orderBy, $start, $limit);

    public function count($condition);
}