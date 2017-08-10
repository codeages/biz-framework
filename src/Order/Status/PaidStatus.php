<?php

namespace Codeages\Biz\Framework\Order\Status;

class PaidStatus extends AbstractStatus
{
    protected $status = 'paid';
    public function getPriorStatus()
    {
        return array('created');
    }

    public function process($orderId, $data)
    {

    }
}