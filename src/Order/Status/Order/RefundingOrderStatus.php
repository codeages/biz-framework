<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class RefundingOrderStatus extends AbstractOrderStatus
{
    const NAME = 'refunding';

    public function getPriorStatus()
    {
        return array(PaidOrderStatus::NAME, FailOrderStatus::NAME, SuccessOrderStatus::NAME);
    }

    public function refunded($data = array())
    {
        return $this->changeStatus(RefundedOrderStatus::NAME);
    }

    public function success($data = array())
    {
        return $this->changeStatus(SuccessOrderStatus::NAME);
    }
}