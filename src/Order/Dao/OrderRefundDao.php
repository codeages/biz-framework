<?php

namespace Codeages\Biz\Framework\Order\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OrderRefundDao extends GeneralDaoInterface
{
    public function findByOrderIds($orderIds);

}