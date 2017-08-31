<?php

namespace Codeages\Biz\Framework\Session\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Session\Dao\OnlineDao;

class OnlineDaoImpl extends GeneralDaoImpl implements OnlineDao
{
    protected $table = 'biz_session_online';



    public function declares()
    {
        return array();
    }
}