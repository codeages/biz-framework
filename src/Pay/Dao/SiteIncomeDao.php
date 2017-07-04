<?php

namespace Codeages\Biz\Framework\Pay\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SiteIncomeDao extends GeneralDaoInterface
{
    public function findByTradeSn($tradeSn);
}