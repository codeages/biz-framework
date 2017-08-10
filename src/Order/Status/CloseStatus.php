<?php

namespace Codeages\Biz\Framework\Order\Status;

class CloseStatus extends AbstractStatus
{
    protected $status = 'close';
    public function getPriorStatus()
    {
        return array('created');
    }

    public function process($orderId, $data = array())
    {

    }
}