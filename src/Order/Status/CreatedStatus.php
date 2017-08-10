<?php

namespace Codeages\Biz\Framework\Order\Status;

class CreatedStatus extends AbstractStatus
{
    protected $status = 'created';
    public function getPriorStatus()
    {
        return array();
    }

    public function process($orderId, $data = array())
    {

    }
}