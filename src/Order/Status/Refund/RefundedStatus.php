<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class RefundedStatus extends AbstractRefundStatus
{
    const NAME = 'refunded';

    public function getPriorStatus()
    {
        return array(RefundingStatus::NAME);
    }

    public function finish()
    {
        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], array(
            'status' => 'finish'
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        foreach ($orderItemRefunds as $orderItemRefund) {
            $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => 'finish'
            ));
        }
        return $orderRefund;
    }
}