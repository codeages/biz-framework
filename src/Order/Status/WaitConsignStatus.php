<?php

namespace Codeages\Biz\Framework\Order\Status;

class WaitConsignStatus extends AbstractStatus
{
    const NAME = 'wait_consign';

    public function getPriorStatus()
    {
        return array(PaidStatus::NAME);
    }

    public function consigned()
    {
        return $this->getOrderDao()->update($this->order['id'], array(
            'status' => ConsignedStatus::NAME,
        ));
    }
}