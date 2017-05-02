<?php

namespace Codeages\Biz\Framework\Scheduler\Dao;

interface JobPoolDao
{
    public function getByName($name = 'default');
}