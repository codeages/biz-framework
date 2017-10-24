<?php

namespace Codeages\Biz\Framework\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderDao extends GeneralDaoInterface
{
    public function getBySn($sn, array $options = array());

    public function findByIds(array $ids);

    public function findBySns(array $orderSns);

    public function countGroupByDate($conditions, $sort, $dateColumn = 'pay_time');
    
    public function sumGroupByDate($column, $conditions, $sort, $dateColumn = 'pay_time');
}