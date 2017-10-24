<?php

namespace Codeages\Biz\Framework\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderItemRefundDao extends GeneralDaoInterface
{
    public function findByOrderRefundId($orderRefundId);

    public function findByConditions($conditions);
}