<?php

namespace Codeages\Biz\Framework\Scheduler\Dao;

interface JobDetailDao
{
    public function findWaitingJobsByLessThanFireTime($fireTime);
}