<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class RefundRefusedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'refund_refused';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->changeStatus(self::NAME);
    }

}