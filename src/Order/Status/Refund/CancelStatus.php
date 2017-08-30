<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class CancelStatus extends AbstractRefundStatus
{
    const NAME = 'cancel';

    public function getName()
    {
        return self::NAME;
    }

    public function getPriorStatus()
    {
        return array(AuditingStatus::NAME);
    }

    public function process()
    {
        $orderRefund = $this->changeStatus(self::NAME);
        $this->getOrderService()->setOrderRefunded($orderRefund['order_id']);
        return $orderRefund;
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }
}