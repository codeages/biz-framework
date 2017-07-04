<?php

namespace Codeages\Biz\Framework\Pay\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

class SiteIncomeDaoImpl extends GeneralDaoImpl implements GeneralDaoInterface
{
    protected $table = 'site_income';

    public function findByTradeSn($tradeSn)
    {
        return $this->findByFields(array(
            'trade_sn' => $tradeSn
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
        );
    }
}