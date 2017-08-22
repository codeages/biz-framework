<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class RefundedStatus extends AbstractRefundStatus
{
    const NAME = 'refunded';

    public function getPriorStatus()
    {
        return array(RefundingStatus::NAME);
    }
}