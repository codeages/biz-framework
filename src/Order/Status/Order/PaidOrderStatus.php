<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class PaidOrderStatus extends AbstractOrderStatus
{
    const NAME = 'paid';

    public function getPriorStatus()
    {
        return array(CreatedOrderStatus::NAME);
    }

    public function consigned()
    {
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => ConsignedOrderStatus::NAME
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => ConsignedOrderStatus::NAME,
            ));
        }
        return $order;
    }
}