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
        $order =$this->getOrderDao()->update($this->order['id'], array(
            'status' => ConsignedStatus::NAME,
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => ConsignedStatus::NAME,
            ));
        }

        return $order;
    }
}