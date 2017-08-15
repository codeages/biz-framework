<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class RefundingStatus extends AbstractRefundStatus
{
    const NAME = 'refunding';

    public function getPriorStatus()
    {
        return array();
    }

    public function refunded($data)
    {
        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], array(
            'deal_time' => time(),
            'deal_user_id' => $this->biz['user']['id'],
            'deal_reason' => empty($data['deal_reason']) ? '' : $data['deal_reason'],
            'status' => RefundedStatus::NAME
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        $updatedOrderItemRefunds = array();
        foreach ($orderItemRefunds as $orderItemRefund) {
            $updatedOrderItemRefunds[] = $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => RefundedStatus::NAME
            ));

            $this->getOrderItemDao()->update($orderItemRefund['order_item_id'], array(
                'refund_status' => RefundedStatus::NAME
            ));
        }

        $orderRefund['orderItemRefunds'] = $updatedOrderItemRefunds;
        return $orderRefund;
    }

    public function closed($data)
    {
        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], array(
            'deal_time' => time(),
            'deal_user_id' => $this->biz['user']['id'],
            'deal_reason' => empty($data['deal_reason']) ? '' : $data['deal_reason'],
            'status' => ClosedStatus::NAME
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        $updatedOrderItemRefunds = array();
        foreach ($orderItemRefunds as $orderItemRefund) {
            $updatedOrderItemRefunds[] = $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => ClosedStatus::NAME
            ));

            $this->getOrderItemDao()->update($orderItemRefund['order_item_id'], array(
                'refund_status' => ClosedStatus::NAME
            ));
        }

        $orderRefund['orderItemRefunds'] = $updatedOrderItemRefunds;
        return $orderRefund;
    }
}