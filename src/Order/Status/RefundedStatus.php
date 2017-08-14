<?php

namespace Codeages\Biz\Framework\Order\Status;

class RefundedStatus extends AbstractStatus
{
    const NAME = 'refunded';

    public function getPriorStatus()
    {
        return array(RefundingStatus::NAME);
    }
}