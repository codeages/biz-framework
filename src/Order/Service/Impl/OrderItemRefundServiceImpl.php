<?php

namespace Codeages\Biz\Framework\Order\Service\Impl;

use Codeages\Biz\Framework\Order\Service\OrderItemRefundService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class OrderItemRefundServiceImpl extends BaseService implements OrderItemRefundService
{
    public function searchRefundItems($conditions, $orderby, $start, $limit)
    {
        return $this->getOrderItemRefundDao()->search($conditions, $orderby, $start, $limit);
    }

    public function countRefundItems($conditions)
    {
        return $this->getOrderItemRefundDao()->count($conditions);
    }

    protected function getOrderItemRefundDao()
    {
        return $this->biz->dao('Order:OrderItemRefundDao');
    }
}