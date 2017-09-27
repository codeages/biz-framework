<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class RefundRefuseOrderStatus extends AbstractOrderStatus
{
    const NAME = 'refund_refuse';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->changeStatus(self::NAME);
    }

}