<?php

namespace Codeages\Biz\Framework\Scheduler\Dao;

interface JobFiredDao
{
    public function getByStatus($status);
}