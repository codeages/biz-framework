<?php

namespace Codeages\Biz\Framework\Order\Status;

class RefundingStatus extends AbstractStatus
{
    const NAME = 'refunding';

    public function getPriorStatus()
    {
        return array(FinishStatus::NAME);
    }

    public function refunded()
    {
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => RefundedStatus::NAME
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => RefundedStatus::NAME,
            ));
        }
        return $order;
    }
}