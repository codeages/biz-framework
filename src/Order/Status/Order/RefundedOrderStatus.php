<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class RefundedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'refunded';

    public function getPriorStatus()
    {
        return array(RefundingOrderStatus::NAME);
    }
}