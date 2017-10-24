<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class SuccessOrderStatus extends AbstractOrderStatus
{
    const NAME = 'success';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => self::NAME,
            'success_data' => $data
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => self::NAME,
            ));
        }

        $deducts = $this->getOrderItemDeductDao()->findByOrderId($this->order['id']);
        foreach ($deducts as $key => $deduct) {
            $deducts[$key] = $this->getOrderItemDeductDao()->update($deduct['id'], array(
                'status' => self::NAME
            ));
        }

        return $order;
    }

    public function refunding($data = array())
    {
        return $this->getOrderStatus(RefundingOrderStatus::NAME)->process($data);
    }
}