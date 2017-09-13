<?php

namespace Codeages\Biz\Framework\Pay\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserCashflowDao extends GeneralDaoInterface
{
    public function findByTradeSn($sn);

    public function sumColumnByConditions($column, $conditions);

    public function searchUserIdsGroupByUserIdOrderBySumColumn($column, $conditions, $sort, $start, $limit);

    public function searchUserIdsGroupByUserIdOrderByBalance($conditions, $sort, $start, $limit);

    public function countUsersByConditions($conditions);
}