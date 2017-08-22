<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class AuditingStatus extends AbstractRefundStatus
{
    const NAME = 'auditing';

    public function getPriorStatus()
    {
        return array();
    }

    public function refunding($data)
    {
        return $this->changeStatusWithData(RefundingStatus::NAME, $data);
    }

    public function refused($data)
    {
        return $this->changeStatusWithData(RefusedStatus::NAME, $data);
    }

    protected function changeStatusWithData($name, $data)
    {
        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], array(
            'deal_time' => time(),
            'deal_user_id' => $this->biz['user']['id'],
            'deal_reason' => empty($data['deal_reason']) ? '' : $data['deal_reason'],
            'status' => $name
        ));

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        $updatedOrderItemRefunds = array();
        foreach ($orderItemRefunds as $orderItemRefund) {
            $updatedOrderItemRefunds[] = $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => $name
            ));

            $this->getOrderItemDao()->update($orderItemRefund['order_item_id'], array(
                'refund_status' => $name
            ));
        }

        $orderRefund['orderItemRefunds'] = $updatedOrderItemRefunds;
        return $orderRefund;
    }
}