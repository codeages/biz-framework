<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class RefusedStatus extends AbstractRefundStatus
{
    const NAME = 'refused';

    public function getPriorStatus()
    {
        return array(AuditingStatus::NAME);
    }
}