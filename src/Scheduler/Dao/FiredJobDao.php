<?php

namespace Codeages\Biz\Framework\Scheduler\Dao;

interface FiredJobDao
{
    public function getByStatus($status);
}