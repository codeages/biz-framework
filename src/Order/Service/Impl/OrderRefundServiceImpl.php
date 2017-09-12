<?php

namespace Codeages\Biz\Framework\Order\Service\Impl;

use Codeages\Biz\Framework\Order\Service\OrderRefundService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class OrderRefundServiceImpl extends BaseService implements OrderRefundService
{
<<<<<<< HEAD
    public function getById($id)
=======
    public function getOrderRefundById($id)
>>>>>>> b25661c6a11085df0643f7a396c769d13c3090af
    {
        return $this->getOrderRefundDao()->get($id);
    }
    
    public function searchRefunds($conditions, $orderby, $start, $limit)
    {
        return $this->getOrderRefundDao()->search($conditions, $orderby, $start, $limit);
    }

    public function countRefunds($conditions)
    {
        return $this->getOrderRefundDao()->count($conditions);
    }

    public function searchRefundItems($conditions, $orderby, $start, $limit)
    {
        return $this->getOrderItemRefundDao()->search($conditions, $orderby, $start, $limit);
    }

    public function countRefundItems($conditions)
    {
        return $this->getOrderItemRefundDao()->count($conditions);
    }

    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }

    protected function getOrderItemRefundDao()
    {
        return $this->biz->dao('Order:OrderItemRefundDao');
    }
}