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

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => WaitConsignStatus::NAME,
            ));
        }
        return $order;
    }
}