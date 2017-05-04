<?php

namespace Codeages\Biz\Framework\Scheduler\Dao;

interface JobDao
{
    public function findWaitingJobsByLessThanFireTime($fireTime);
}