<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class RefundingStatus extends AbstractRefundStatus
{
    const NAME = 'refunding';

    public function getPriorStatus()
    {
        return array(CreatedStatus::NAME);
    }

    public function refunded($data)
    {
        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], array(
            'deal_time' => time(),
            'deal_user_id' => $this->biz['user']['id'],
            'deal_reason' => empty($data['deal_reason']) ? '' : $data['deal_reason'],
            'status' => 'refunded'
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        foreach ($orderItemRefunds as $orderItemRefund) {
            $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => 'refunded'
            ));
        }

        return $orderRefund;
    }

    public function closed($data)
    {
        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], array(
            'deal_time' => time(),
            'deal_user_id' => $this->biz['user']['id'],
            'deal_reason' => empty($data['deal_reason']) ? '' : $data['deal_reason'],
            'status' => 'closed'
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        foreach ($orderItemRefunds as $orderItemRefund) {
            $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => 'closed'
            ));
        }

        return $orderRefund;
    }
}