<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class SuccessOrderStatus extends AbstractOrderStatus
{
    const NAME = 'success';

    public function getPriorStatus()
    {
        return array(FailOrderStatus::NAME, PaidOrderStatus::NAME);
    }

    public function refunding($data = array())
    {
        return $this->changeStatus(RefundingOrderStatus::NAME);
    }
}