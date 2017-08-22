<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class PaidOrderStatus extends AbstractOrderStatus
{
    const NAME = 'paid';

    public function getPriorStatus()
    {
        return array(PayingOrderStatus::NAME);
    }

    public function success($data = array())
    {
        return $this->changeStatus(SuccessOrderStatus::NAME);
    }

    public function fail($data = array())
    {
        return $this->changeStatus(FailOrderStatus::NAME);
    }
}