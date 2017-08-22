<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class FailOrderStatus extends AbstractOrderStatus
{
    const NAME = 'fail';

    public function getPriorStatus()
    {
        return array(PaidOrderStatus::NAME);
    }

    public function success($data = array())
    {
        return $this->changeStatus(SuccessOrderStatus::NAME);
    }
}