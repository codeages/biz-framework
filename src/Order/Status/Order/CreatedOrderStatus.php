<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class CreatedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'created';

    public function getPriorStatus()
    {
        return array();
    }

    public function closed($data = array())
    {
        $closeTime = time();
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => ClosedOrderStatus::NAME,
            'close_time' => $closeTime
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => ClosedOrderStatus::NAME,
                'close_time' => $closeTime
            ));
        }

        return $order;
    }

    public function paying($data = array())
    {
        return $this->changeStatus(PayingOrderStatus::NAME);
    }
}