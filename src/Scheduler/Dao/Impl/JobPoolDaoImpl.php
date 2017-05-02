<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Dao\JobPoolDao;

class JobPoolDaoImpl extends GeneralDaoImpl implements JobPoolDao
{
    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime')
        );
    }
}