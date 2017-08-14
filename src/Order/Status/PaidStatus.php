<?php

namespace Codeages\Biz\Framework\Order\Status;

class PaidStatus extends AbstractStatus
{
    const NAME = 'paid';

    public function getPriorStatus()
    {
        return array(CreatedStatus::NAME);
    }

    public function waitConsign()
    {
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => WaitConsignStatus::NAME
        ));
        return $order;
    }
}