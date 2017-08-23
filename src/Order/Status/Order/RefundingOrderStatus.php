<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class RefundingOrderStatus extends AbstractOrderStatus
{
    const NAME = 'refunding';

    public function getName()
    {
        return self::NAME;
    }

    public function getPriorStatus()
    {
        return array(PaidOrderStatus::NAME, FailOrderStatus::NAME, SuccessOrderStatus::NAME);
    }

    public function process($data = array())
    {
        return $this->changeStatus(self::NAME);
    }

    public function refunded($data = array())
    {
        return $this->getOrderStatus(RefundedOrderStatus::NAME)->process($data);
    }

    public function success($data = array())
    {
        return $this->getOrderStatus(SuccessOrderStatus::NAME)->process($data);
    }
}